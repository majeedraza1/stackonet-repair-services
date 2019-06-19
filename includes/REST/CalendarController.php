<?php

namespace Stackonet\REST;

use Exception;
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

		$orders_counts = $this->get_orders_counts();
		$leads_counts  = $this->get_leads_counts();
		$events        = array_merge( $orders_counts, $leads_counts );

		return $this->respondOK( [ 'events' => $events ] );
	}

	/**
	 * Get order counts
	 *
	 * @return array
	 */
	private function get_orders_counts() {
		global $wpdb;
		$sql     = "SELECT COUNT(ID) counts, DATE(post_date) created FROM {$wpdb->posts}";
		$sql     .= " WHERE 1=1 AND post_type = 'shop_order'";
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
	 * @return array
	 */
	public function get_leads_counts() {
		$results = ( new Appointment() )->get_counts_group_by_created_at();

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

				];
			}
		} catch ( Exception $e ) {
			Logger::log( $e->getMessage() );
		}

		return $items;
	}

	private function get_lead_data( $date ) {
		$items = ( new Appointment() )->find_by_date( $date );

		return $items;
	}
}
