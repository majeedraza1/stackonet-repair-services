<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use Stackonet\Models\Appointment;
use Stackonet\Models\CarrierStore;
use Stackonet\Models\SmsTemplate;
use Stackonet\Models\Survey;
use Stackonet\Modules\SupportTicket\SupportAgent;
use Stackonet\Supports\ArrayHelper;
use Stackonet\Supports\Logger;
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
		register_rest_route( $this->namespace, '/sms/template', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_templates' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_template' ] ],
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_template' ] ],
		] );
		register_rest_route( $this->namespace, '/sms/template/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_template' ] ],
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

		$response = [ 'data_source' => $source, 'date_from' => $date_from, 'date_to' => $date_to, 'items' => [] ];

		if ( 'orders' == $source ) {
			$response['items'] = $this->get_orders( $date_from, $date_to );
		}

		if ( 'support-agents' == $source ) {
			$response['items'] = $this->get_support_agents( $date_from, $date_to );
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

		if ( 'all' == $source ) {
			$orders         = $this->get_orders( $date_from, $date_to );
			$agents         = $this->get_support_agents( $date_from, $date_to );
			$survey         = $this->get_survey( $date_from, $date_to );
			$appointment    = $this->get_appointment( $date_from, $date_to );
			$carrier_stores = $this->get_carrier_stores( $date_from, $date_to );

			$response['items'] = array_merge( $orders, $agents, $survey, $appointment, $carrier_stores );
		}

		if ( ! empty( $response['items'] ) ) {
			$response['items'] = array_values( ArrayHelper::unique_multidim_array( $response['items'], 'phone' ) );
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
		$numbers = $request->get_param( 'numbers' );
		$content = $request->get_param( 'content' );

		if ( ! ( is_array( $numbers ) && count( $numbers ) ) ) {
			return $this->respondUnprocessableEntity( null, 'SMS receivers numbers is required.' );
		}

		if ( empty( $content ) ) {
			return $this->respondUnprocessableEntity( null, 'SMS content is required.' );
		}

		$reminder = stackonet_repair_services()->bulk_sms();
		foreach ( $numbers as $number ) {
			$data = [ 'number' => $number, 'content' => $content ];
			$reminder->push_to_queue( $data );
		}
		$reminder->save();
		$reminder->dispatch();

		return $this->respondOK( $request->get_params() );
	}

	/**
	 * Get sms templates
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_templates( $request ) {
		$response = SmsTemplate::get_templates();

		return $this->respondOK( [ 'items' => $response ] );
	}

	/**
	 * Create new sms template
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function create_template( $request ) {
		$content = $request->get_param( 'content' );

		$response = SmsTemplate::create( [ 'content' => $content ] );

		return $this->respondCreated( $response );
	}

	/**
	 * Create new sms template
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_template( $request ) {
		$id      = $request->get_param( 'id' );
		$content = $request->get_param( 'content' );

		$item = SmsTemplate::get_template( $id );
		if ( empty( $item ) ) {
			return $this->respondNotFound( null, 'Item not found' );
		}

		$response = SmsTemplate::update( [ 'id' => $id, 'content' => $content ] );

		return $this->respondOK( $response );
	}

	/**
	 * Create new sms template
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_template( $request ) {
		$id   = $request->get_param( 'id' );
		$item = SmsTemplate::get_template( $id );
		if ( empty( $item ) ) {
			return $this->respondNotFound( null, 'Item not found' );
		}

		if ( SmsTemplate::delete( $id ) ) {
			return $this->respondOK();
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Get orders
	 *
	 * @param string $date_from
	 * @param string $date_to
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
				if ( empty( $_order->get_billing_phone() ) ) {
					continue;
				}

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
	 * @param $date_from
	 * @param $date_to
	 *
	 * @return array
	 */
	private function get_support_agents( $date_from, $date_to ) {
		$agents = SupportAgent::get_all();

		$items = [];
		foreach ( $agents as $agent ) {
			if ( empty( $agent->get_phone_number() ) ) {
				continue;
			}
			$registered = $agent->get_user()->user_registered;
			$items[]    = [
				'agent_id' => $agent->get_user()->ID,
				'name'     => $agent->get_user()->display_name,
				'phone'    => $agent->get_phone_number(),
				'date'     => $agent->get_user()->user_registered,
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
		$survey = ( new Survey() )->find( [
			'per_page'  => 100,
			'date_from' => $date_from,
			'date_to'   => $date_to,
		] );
		$items  = [];
		foreach ( $survey as $agent ) {
			if ( empty( $agent->get( 'phone' ) ) ) {
				continue;
			}
			$items[] = [
				'agent_id' => $agent->get( 'id' ),
				'name'     => $agent->get( 'email' ),
				'phone'    => $agent->get( 'phone' ),
				'date'     => date( 'Y-m-d', strtotime( $agent->get( 'created_at' ) ) ),
			];
		}

		return $items;
	}

	/**
	 * Get appointment
	 *
	 * @param string $date_from
	 * @param string $date_to
	 *
	 * @return array
	 */
	private function get_appointment( $date_from, $date_to ) {
		$survey = ( new Appointment() )->find( [
			'per_page'  => 100,
			'date_from' => $date_from,
			'date_to'   => $date_to,
		] );
		$items  = [];
		foreach ( $survey as $agent ) {
			if ( empty( $agent->get( 'phone' ) ) ) {
				continue;
			}
			$items[] = [
				'id'    => $agent->get( 'id' ),
				'name'  => $agent->get( 'store_name' ),
				'phone' => $agent->get( 'phone' ),
				'date'  => date( 'Y-m-d', strtotime( $agent->get( 'created_at' ) ) ),
			];
		}

		return $items;
	}

	/**
	 * Get carrier stores
	 *
	 * @param string $date_from
	 * @param string $date_to
	 *
	 * @return array
	 */
	private function get_carrier_stores( $date_from, $date_to ) {
		$survey = ( new CarrierStore() )->find( [
			'per_page'  => 100,
			'date_from' => $date_from,
			'date_to'   => $date_to,
		] );
		$items  = [];
		foreach ( $survey as $agent ) {
			if ( empty( $agent->get( 'phone' ) ) ) {
				continue;
			}
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
