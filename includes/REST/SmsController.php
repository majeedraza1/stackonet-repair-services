<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use Stackonet\Models\Appointment;
use Stackonet\Models\CarrierStore;
use Stackonet\Models\Survey;
use Stackonet\Modules\SupportTicket\SupportAgent;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class SmsController extends ApiController {

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
		register_rest_route( $this->namespace, '/sms', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
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
		$source = $request->get_param( 'source' );
		$type   = $request->get_param( 'type' );
		$from   = $request->get_param( 'from' );
		$to     = $request->get_param( 'to' );

		$valid_sources = [ 'all', 'support-agents', 'orders', 'survey', 'appointment', 'carrier-stores' ];
		$valid_types   = [ 'today', 'yesterday', 'this-week', 'last-week', 'this-month', 'last-month', 'custom' ];

		if ( ! in_array( $source, $valid_sources ) ) {
			return $this->respondUnprocessableEntity( null, 'Invalid data source.' );
		}

		if ( ! in_array( $type, $valid_types ) ) {
			return $this->respondUnprocessableEntity( null, 'Invalid filter type.' );
		}

		$now_timestamp = current_time( 'timestamp' );
		$now           = current_time( 'mysql' );

		$today_start = date( 'Y-m-d', $now_timestamp ) . ' 00:00:01';

		$date_from = $today_start;
		$date_to   = $now;

		if ( 'yesterday' == $type ) {
			$date_from = date( 'Y-m-d', strtotime( 'yesterday' ) ) . ' 00:00:01';
			$date_to   = date( 'Y-m-d', strtotime( 'yesterday' ) ) . ' 12:00:00';
		}

		$dateTime = new DateTime();

		if ( 'this-week' == $type ) {
			$dateTime->modify( 'last monday' );
			$date_from = $dateTime->format( 'Y-m-d' ) . ' 00:00:01';
			$date_to   = $now;
		}

		if ( 'last-week' == $type ) {
			$dateTime->modify( '- 6 days' );
			$date_from = $dateTime->format( 'Y-m-d' ) . ' 00:00:01';
			$date_to   = $now;
		}

		if ( 'this-month' == $type ) {
			$dateTime->modify( 'first day of this month' );
			$date_from = $dateTime->format( 'Y-m-d' ) . ' 00:00:01';
			$date_to   = $now;
		}

		if ( 'last-month' == $type ) {
			$dateTime->modify( 'first day of last month' );
			$date_from = $dateTime->format( 'Y-m-d' ) . ' 00:00:01';
			$dateTime->modify( 'last day of this month' );
			$date_to = $dateTime->format( 'Y-m-d' ) . ' 12:00:00';
		}

		if ( 'custom' == $type ) {
			$date_from = $from . ' 00:00:01';
			$date_to   = $to . ' 12:00:00';
		}

		$items    = [];
		$response = [ 'data_source' => $source, 'date_from' => $date_from, 'date_to' => $date_to, 'items' => [] ];

		if ( 'orders' == $source ) {
			$response['items'] = $this->get_orders( $date_from, $date_to );
		}

		if ( 'support-agents' == $source ) {
			$response['items'] = $this->get_support_agents();
		}

		if ( 'survey' == $source ) {
			$response['items'] = $this->get_survey( $date_from, $date_to );
		}

		if ( 'appointment' == $source ) {
			$response['items'] = $this->get_appointment( $date_from, $date_to );
		}

		if ( 'carrier-stores' == $source ) {
			$response['items'] = $this->get_carrier_stores( $date_from, $date_to );
		}


		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		return $this->respondOK();
	}

	/**
	 * Get orders
	 *
	 * @param $date_from
	 * @param $date_to
	 *
	 * @return array
	 */
	private function get_orders( $date_from, $date_to ) {
		$orders_ids = wc_get_orders( array(
			'limit'        => 100,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'return'       => 'ids',
			'date_created' => sprintf(
				'%s...%s',
				date( 'Y-m-d', strtotime( $date_from ) ),
				date( 'Y-m-d', strtotime( $date_to ) )
			)
		) );

		$items = [];

		if ( $orders_ids ) {
			foreach ( $orders_ids as $order_id ) {
				$_order = wc_get_order( $order_id );

				$items[] = [
					'order_id' => $order_id,
					'name'     => $_order->get_formatted_billing_full_name(),
					'phone'    => $_order->get_billing_phone(),
					'date'     => $_order->get_date_created()->format( 'Y-m-d' ),
				];
			}
		}

		return $items;
	}

	/**
	 * Get support agents
	 *
	 * @return array
	 */
	private function get_support_agents() {
		$agents = SupportAgent::get_all();

		$items = [];
		foreach ( $agents as $agent ) {
			$items[] = [
				'agent_id' => $agent->get_user()->ID,
				'name'     => $agent->get_user()->display_name,
				'phone'    => $agent->get_phone_number(),
				'date'     => '',
			];
		}

		return $items;
	}

	/**
	 * Get survey data
	 *
	 * @param $date_from
	 * @param $date_to
	 *
	 * @return array
	 */
	private function get_survey( $date_from, $date_to ) {
		$survey = ( new Survey() )->find( [ 'per_page' => 100 ] );
		$items  = [];
		foreach ( $survey as $agent ) {
			$items[] = [
				'agent_id' => $agent->get( 'id' ),
				'name'     => $agent->get( 'email' ),
				'phone'    => $agent->get( 'phone' ),
				'date'     => date( 'Y-m-d', strtotime( $agent->get( 'created_at' ) ) ),
			];
		}

		return $items;
	}

	private function get_appointment( $date_from, $date_to ) {
		$survey = ( new Appointment() )->find( [ 'per_page' => 100 ] );
		$items  = [];
		foreach ( $survey as $agent ) {
			$items[] = [
				'id'    => $agent->get( 'id' ),
				'name'  => $agent->get( 'store_name' ),
				'phone' => $agent->get( 'phone' ),
				'date'  => date( 'Y-m-d', strtotime( $agent->get( 'created_at' ) ) ),
			];
		}

		return $items;
	}

	private function get_carrier_stores( $date_from, $date_to ) {
		$survey = ( new CarrierStore() )->find( [ 'per_page' => 100 ] );
		$items  = [];
		foreach ( $survey as $agent ) {
			$items[] = [
				'id'    => $agent->get( 'id' ),
				'name'  => $agent->get( 'name' ),
				'phone' => $agent->get( 'phone' ),
				'date'  => date( 'Y-m-d', strtotime( $agent->get( 'created_at' ) ) ),
			];
		}

		return $items;
	}
}
