<?php

namespace Stackonet\Integrations;

use Stackonet\Supports\Logger;

defined( 'ABSPATH' ) or exit;

class Twilio {

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

			add_action( 'stackonet_order_created', [ self::$instance, 'send_sms' ] );
		}

		return self::$instance;
	}

	/**
	 * Send sms on order creation
	 *
	 * @param \WC_Order $order
	 */
	public function send_sms( $order ) {
		if ( ! function_exists( 'wc_twilio_sms' ) ) {
			Logger::log( 'wc_twilio_sms is not available' );

			return;
		}

		$this->send_sms_to_admin( $order );
		$this->send_sms_to_customer( $order );
	}

	/**
	 * Send SMS to customer
	 *
	 * @param \WC_Order $order
	 */
	public function send_sms_to_customer( $order ) {
		$message = "Thank You for your %device_name% %device_model% service order at %prefer_date% %prefer_time%!";
		$message .= " For any questions or appointment changes please text or call 561-377-6341.";

		$to      = $order->get_billing_phone();
		$message = $this->variable_replace( $message, $order );


		try {
			$billing_country = $order->get_meta( 'billing_country', true );
			$response        = wc_twilio_sms()->get_api()->send( $to, $message, $billing_country );
		} catch ( \Exception $e ) {
			// Set status to error message
			$status = $e->getMessage();
			Logger::log( $status );
		}
	}

	/**
	 * Send SMS to admin
	 *
	 * @param \WC_Order $order
	 */
	public function send_sms_to_admin( $order ) {
		$message = "NEW ORDER %order_id%: %customer_name% has order a %device_name% %device_model%";
		$message .= " %device_issues% at %customer_address%. Please arrive by %prefer_date% %prefer_time%.";

		$to      = get_option( 'admin_email' );
		$message = $this->variable_replace( $message, $order );


		try {
			$response = wc_twilio_sms()->get_api()->send( $to, $message );
		} catch ( \Exception $e ) {
			// Set status to error message
			$status = $e->getMessage();
			Logger::log( $status );
		}
	}


	/**
	 * @param string $message
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	public function variable_replace( $message, $order ) {
		$preferred_date = $order->get_meta( '_preferred_service_date', true );
		$preferred_time = $order->get_meta( '_preferred_service_time_range', true );
		$device_title   = $order->get_meta( '_device_title', true );
		$device_model   = $order->get_meta( '_device_model', true );
		$device_color   = $order->get_meta( '_device_color', true );
		$device_issues  = $order->get_meta( '_device_issues', true );
		$device_issues  = is_array( $device_issues ) ? implode( ', ', $device_issues ) : $device_issues;

		$first_name = $order->get_billing_first_name();
		$last_name  = $order->get_billing_last_name();

		$display_name = '';
		if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
			$display_name = $first_name . ' ' . $last_name;
		} elseif ( ! empty( $last_name ) ) {
			$display_name = $last_name;
		} elseif ( ! empty( $first_name ) ) {
			$display_name = $first_name;
		}

		$city     = $order->get_billing_city();
		$state    = $order->get_billing_state();
		$postcode = $order->get_billing_postcode();

		$customer_address = $order->get_billing_address_1();
		if ( ! empty( $city ) ) {
			$customer_address .= ', ' . $city;
		}
		if ( ! empty( $state ) ) {
			$customer_address .= ', ' . $state;
		}
		if ( ! empty( $postcode ) ) {
			$customer_address .= ' ' . $postcode;
		}

		$replacements = array(
			'%order_id%'         => $order->get_id(),
			'%prefer_date%'      => $preferred_date,
			'%prefer_time%'      => $preferred_time,
			'%device_name%'      => $device_title,
			'%device_model%'     => $device_model,
			'%customer_name%'    => $display_name,
			'%device_issues%'    => $device_issues,
			'%customer_address%' => $customer_address,
		);

		return str_replace( array_keys( $replacements ), $replacements, $message );
	}
}
