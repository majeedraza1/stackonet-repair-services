<?php

namespace Stackonet\REST;

use Stackonet\Models\Reschedule;
use Exception;
use WC_Order;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class OrderRescheduleController extends ApiController {


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
		register_rest_route( $this->namespace, '/reschedule', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'reschedule_order' ],
			],
		] );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 */
	public function reschedule_order( $request ) {
		$order_id   = (int) $request->get_param( 'order_id' );
		$token      = $request->get_param( 'token' );
		$date       = $request->get_param( 'date' );
		$time_range = $request->get_param( 'time_range' );


		if ( empty( $order_id ) || empty( $token ) || empty( $date ) || empty( $time_range ) ) {
			return $this->respondUnprocessableEntity();
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound();
		}

		if ( $token != $order->get_meta( '_reschedule_hash', true ) ) {
			return $this->respondUnauthorized();
		}

		$data = [
			'date'       => $date,
			'time'       => $time_range,
			'user'       => $order->get_customer_id(),
			'created_by' => 'customer',
		];

		// Update order metadata
		Reschedule::update_service_date( $order, $data );

		// Set background process for sms and email.
		$reschedule = stackonet_repair_services()->async_reschedule();
		$reschedule->push_to_queue( [ 'order_id' => $order_id, 'data' => $data, ] );
		$reschedule->save();
		$reschedule->dispatch();

		return $this->respondOK( 'Order has been rescheduled successfully.' );
	}
}