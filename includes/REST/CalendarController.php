<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Models\Appointment;
use Stackonet\Supports\Logger;
use WC_Order;
use WC_Order_Query;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CalendarController extends ApiController {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/calendar', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function get_items( $request ) {
		$date = $request->get_param( 'date' );
		$type = $request->get_param( 'type' );

		if ( ! empty( $date ) && ! empty( $type ) ) {
			if ( 'order' == $type ) {
				$order_data = $this->get_order_data( $date );

				return $this->respondOK( $order_data );
			}

			if ( 'lead' == $type ) {
				$order_data = $this->get_lead_data( $date );

				return $this->respondOK( $order_data );
			}

		}

		$month    = $request->get_param( 'month' );
		$month    = ! empty( $month ) ? $month : date( 'm', time() );
		$year     = $request->get_param( 'year' );
		$year     = ! empty( $year ) ? $year : date( 'Y', time() );
		$dateTime = new DateTime();
		$dateTime->setDate( $year, $month, 1 );
		$start_date = $dateTime->format( 'Y-m-d' );
		$end_date   = $dateTime->format( 'Y-m-t' );


		$orders_counts = $this->get_orders_counts( $start_date, $end_date );
		$leads_counts  = $this->get_leads_counts( $start_date, $end_date );

		$events = array_merge( $orders_counts, $leads_counts );

		$dates    = range( 1, intval( $dateTime->format( 't' ) ) );
		$yearNum  = intval( $dateTime->format( 'Y' ) );
		$monthNum = intval( $dateTime->format( 'm' ) );

		$now     = new DateTime();
		$dateNum = intval( $now->format( 'd' ) );

		$_orders_counts = [];
		foreach ( $orders_counts as $order_count ) {
			$_time = strtotime( $order_count['date'] );
			if ( date( 'Y', $_time ) == $yearNum && $monthNum == date( 'm', $_time ) ) {
				$_dateNum = intval( date( 'd', $_time ) );

				$_orders_counts[ $_dateNum ] = intval( $order_count['counts'] );
			}
		}

		$_leads_counts = [];
		foreach ( $leads_counts as $order_count ) {
			$_time = strtotime( $order_count['date'] );
			if ( date( 'Y', $_time ) == $yearNum && $monthNum == date( 'm', $_time ) ) {
				$_dateNum = intval( date( 'd', $_time ) );

				$_leads_counts[ $_dateNum ] = intval( $order_count['counts'] );
			}
		}

		$now = new DateTime();

		$orders_data = [];
		$leads_data  = [];
		foreach ( $dates as $index ) {
			if ( $yearNum <= intval( $now->format( 'Y' ) ) ) {

				if ( $monthNum > intval( $now->format( 'm' ) ) ) {
					$_default = null;
				} elseif ( $monthNum == intval( $now->format( 'm' ) ) ) {
					$_default = ( $index <= $dateNum ) ? 0 : null;
				} else {
					$_default = 0;
				}

			} else {
				$_default = null;
			}

			$orders_data[ $index ] = isset( $_orders_counts[ $index ] ) ? $_orders_counts[ $index ] : $_default;
			$leads_data[ $index ]  = isset( $_leads_counts[ $index ] ) ? $_leads_counts[ $index ] : $_default;
		}

		$chartData = [
			'datasets' => [
				[
					'label'           => 'Orders',
					'borderColor'     => '#f58730',
					'backgroundColor' => 'transparent',
					'data'            => array_values( $orders_data )
				],
				[
					'label'           => 'Leads',
					'borderColor'     => '#ff0000',
					'backgroundColor' => 'transparent',
					'data'            => array_values( $leads_data )
				],
			],
			'labels'   => $dates,
		];

		return $this->respondOK( [ 'chartData' => $chartData, 'events' => $events, ] );
	}

	/**
	 * Get order counts
	 *
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return array
	 */
	private function get_orders_counts( $start_date = null, $end_date = null ) {
		global $wpdb;
		$sql = "SELECT COUNT(ID) AS counts, DATE(post_date) AS created FROM {$wpdb->posts}";
		$sql .= " WHERE 1=1 AND post_type = 'shop_order'";
		if ( ! empty( $start_date ) && ! empty( $start_date ) ) {
			$sql .= $wpdb->prepare( " AND DATE(post_date) between %s and %s", $start_date, $end_date );
		}
		$sql     .= " GROUP BY DATE(post_date)";
		$sql     .= " ORDER BY DATE(post_date) DESC;";
		$results = $wpdb->get_results( $sql, ARRAY_A );

		$items = [];
		foreach ( $results as $result ) {
			$items[] = [
				'type'   => 'order',
				'date'   => $result['created'],
				'counts' => $result['counts'],
			];
		}

		return $items;
	}

	/**
	 * Get leads counts
	 *
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return array
	 */
	public function get_leads_counts( $start_date = null, $end_date = null ) {
		$results = ( new Appointment() )->get_counts_group_by_created_at( $start_date, $end_date );

		$items = [];
		foreach ( $results as $result ) {
			$items[] = [
				'type'   => 'lead',
				'date'   => $result['created'],
				'counts' => $result['counts'],
			];
		}

		return $items;
	}

	/**
	 * Get order data
	 *
	 * @param string $date
	 *
	 * @return array
	 */
	private function get_order_data( $date ) {
		$query = new WC_Order_Query();
		$query->set( 'date_created', $date );
		$items = [];
		try {
			/** @var WC_Order[] $orders */
			$orders = $query->get_orders();
			foreach ( $orders as $order ) {
				$items[] = [
					'id'                 => $order->get_id(),
					'customer_full_name' => $order->get_formatted_billing_full_name(),
					'issues'             => $order->get_meta( '_device_issues', true ),
					'address'            => $order->get_formatted_billing_address(),
					'address_map_url'    => $order->get_shipping_address_map_url(),
					'ticket_id'          => $order->get_meta( '_support_ticket_id', true ),
					'order_total'        => $order->get_total(),
					'latitude_longitude' => GoogleMap::get_customer_latitude_longitude_from_order( $order ),
				];
			}
		} catch ( Exception $e ) {
			Logger::log( $e->getMessage() );
		}

		return $items;
	}

	/**
	 * Get lead data
	 *
	 * @param string $date
	 *
	 * @return array
	 */
	private function get_lead_data( $date ) {
		$_items = ( new Appointment() )->find_by_date( $date );
		$items  = [];
		foreach ( $_items as $item ) {
			$items[] = [
				'id'                 => $item->get( 'id' ),
				'store_name'         => $item->get( 'store_name' ),
				'device_issues'      => $item->get( 'device_issues' ),
				'full_address'       => $item->get( 'full_address' ),
				'latitude_longitude' => GoogleMap::get_appointment_latitude_longitude( $item ),
			];
		}

		return $items;
	}
}
