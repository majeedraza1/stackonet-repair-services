<?php

namespace Stackonet\Frontend;

use Stackonet\Models\Settings;
use WC_Order;

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
		if ( ! $order instanceof WC_Order ) {
			die( 'No record found.' );
		}

		$_token = $order->get_meta( '_reschedule_hash', true );

		if ( $token != $_token ) {
			die( 'Link has been expired.' );
		}

		if ( ! $order->needs_payment() ) {
			die( 'Payment is already complete. Link has been expired.' );
		}

		$device_title = $order->get_meta( '_device_title', true );
		$device_model = $order->get_meta( '_device_model', true );
		$device_color = $order->get_meta( '_device_color', true );

		$device = sprintf( '%s %s (%s)', $device_title, $device_model, $device_color );

		$amount = 0;
		$fees   = [];
		$_fees  = $order->get_fees();
		foreach ( $_fees as $fee ) {
			$amount += $fee->get_total();
			$fees[] = [
				'name'  => $fee->get_name(),
				'total' => wc_price( $fee->get_total() ),
			];
		}

		$taxes  = [];
		$_taxes = $order->get_tax_totals();

		foreach ( $_taxes as $tax ) {
			$taxes[] = [
				'name'  => $tax->label,
				'total' => $tax->formatted_amount,
			];
		}

		wp_localize_script( 'stackonet-payment-form', 'StackonetPayment', [
			'order_id'        => $order->get_id(),
			'application_id'  => Settings::get_square_payment_application_id(),
			'location_id'     => Settings::get_square_payment_location_id(),
			'pay_button_text' => 'Pay ' . wc_price( $order->get_total() ),
			'thank_you_url'   => get_permalink( Settings::get_payment_thank_you_page_id() ),
			'order'           => [
				'id'            => $order->get_id(),
				'device'        => $device,
				'device_issues' => $order->get_meta( '_device_issues', true ),
				'fees'          => $fees,
				'fees_total'    => wc_price( $amount ),
				'taxes'         => $taxes,
				'order_total'   => $order->get_formatted_order_total(),
			],
			'customer'        => [
				'name'    => $order->get_formatted_billing_full_name(),
				'email'   => $order->get_billing_email(),
				'phone'   => $order->get_billing_phone(),
				'address' => $order->get_formatted_billing_address(),
			]
		] );

		return '<div id="stackonet_payment_form"></div>';
	}
}
