<?php

namespace Stackonet\REST;

use SquareConnect\Model\ChargeResponse;
use Stackonet\Integrations\Square;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class CheckoutController extends ApiController {
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
		register_rest_route( $this->namespace, '/checkout', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'checkout' ] ],
		] );
	}

	/**
	 * Get one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function checkout( $request ) {
		$order_id = (int) $request->get_param( 'order' );
		$nonce    = $request->get_param( 'nonce' );

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound( null, 'No order found.' );
		}

		$needs_payment = $order->needs_payment();

		$square = new Square();
		$result = $square->charge_card_nonce( $nonce, intval( $order->get_total() ) );
		if ( $result instanceof ChargeResponse ) {
			$transaction = $result->getTransaction();
			var_dump( $transaction->getId() );
		}

		return $this->respondOK( $request->get_params() );
	}
}
