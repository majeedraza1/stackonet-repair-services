<?php

namespace Stackonet\Frontend;

defined( 'ABSPATH' ) || exit;

class PaymentPage {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_shortcode( 'stackonet_payment_form', [ self::$instance, 'payment_form' ] );
		}

		return self::$instance;
	}

	/**
	 * @return string
	 */
	public function payment_form() {
		wp_enqueue_style( 'stackonet-payment-form' );
		wp_enqueue_script( 'stackonet-payment-form' );

		$order_id = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : 0;
		$token    = isset( $_GET['token'] ) ? strip_tags( $_GET['token'] ) : null;

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			die( 'No record found.' );
		}

		$_token = $order->get_meta( '_reschedule_hash', true );

		if ( $token != $_token ) {
			die( 'Link has been expired.' );
		}

		wp_localize_script( 'stackonet-payment-form', 'StackonetPayment', [
			'order_id'        => $order->get_id(),
			'location_id'     => "CBASEF3_KWP_i8_DGzvjsrSYmwsgAQ",
			'application_id'  => "sandbox-sq0idp-ZWCqBQE_su61oWsKiBi5cw",
			'pay_button_text' => 'Pay ' . wc_price( $order->get_total() ),
			'order'           => [],
		] );

		return '<div id="stackonet_payment_form"></div>';
	}
}
