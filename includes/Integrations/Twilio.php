<?php

namespace Stackonet\Integrations;

use Exception;
use Stackonet\Supports\Logger;
use Stackonet\Supports\Utils;
use WC_Order;

defined( 'ABSPATH' ) or exit;

class Twilio {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Admin hone numbers
	 *
	 * @var array
	 */
	private $admin_numbers = [ '+15613776341', '+15613776340', '+15617134700' ];

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->init_dev_data();

			add_action( 'stackonet_order_created', [ self::$instance, 'send_sms' ] );
		}

		return self::$instance;
	}

	/**
	 * Twilio constructor.
	 */
	public function __construct() {
		$this->init_dev_data();
	}

	/**
	 * Initiate development data
	 */
	public function init_dev_data() {
		if ( defined( 'WP_DEBUG_LOCAL' ) && WP_DEBUG_LOCAL ) {
			$this->admin_numbers = [ '+8801701309039' ];
		}
	}

	/**
	 * Send sms on order creation
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
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
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_sms_to_customer( $order ) {
		$message = "Thank You for your %device_name% %device_model% service order at %prefer_date% %prefer_time%!";
		$message .= " For any questions or appointment changes please text or call %support_phone%.";

		$to      = $order->get_billing_phone();
		$message = $this->variable_replace( $message, $order );


		try {
			$billing_country = $order->get_meta( 'billing_country', true );
			$response        = wc_twilio_sms()->get_api()->send( $to, $message, $billing_country );
		} catch ( Exception $e ) {
			// Set status to error message
			$status = $e->getMessage();
			Logger::log( $status );
		}
	}

	/**
	 * Send SMS to admin
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_sms_to_admin( $order ) {
		$message = "NEW ORDER %order_id%: %customer_name% has order a %device_name% %device_model%";
		$message .= " %device_issues% at %customer_address%. Please arrive by %prefer_date% %prefer_time%.";
		$message .= ' ' . $this->get_billing_address_map_url( $order );

		$message = $this->variable_replace( $message, $order );

		foreach ( $this->admin_numbers as $to ) {
			try {
				$response = wc_twilio_sms()->get_api()->send( $to, $message );
			} catch ( Exception $e ) {
				// Set status to error message
				$status = $e->getMessage();
				Logger::log( $status );
			}
		}
	}

	/**
	 * Send re-schedule mail
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reschedule_sms( $order ) {
		$this->send_reschedule_sms_to_customer( $order );
		$this->send_reschedule_sms_to_admin( $order );
	}

	/**
	 * Send Re-Schedule mail to customer
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reschedule_sms_to_customer( $order ) {
		$message = "Thank You for your  %device_name% %device_model% service order at %prefer_date% %prefer_time%!";
		$message .= " For any questions, If you need to reschedule please Click here %reschedule_url%";
		$message .= " or please text or call %support_phone%.";

		$to      = $order->get_billing_phone();
		$message = $this->variable_replace( $message, $order );

		try {
			$billing_country = $order->get_meta( 'billing_country', true );
			$response        = wc_twilio_sms()->get_api()->send( $to, $message, $billing_country );
		} catch ( Exception $e ) {
			// Set status to error message
			$status = $e->getMessage();
			Logger::log( $status );
		}
	}

	/**
	 * Send Re-Schedule mail to admin
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reschedule_sms_to_admin( $order ) {
		$_date     = get_post_meta( $order->get_id(), '_reschedule_date_time', true );
		$_date     = is_array( $_date ) ? $_date : [];
		$last_date = end( $_date );

		$message = "NEW APPOINTMENT CHANGES %order_id%: %customer_name% has rescheduled a appointment ";
		$message .= "%device_issues% at %customer_address%. Please arrive by ";
		$message .= sprintf( "%s %s. ", $last_date['date'], $last_date['time'] );
		$message .= $this->get_billing_address_map_url( $order );

		$message = $this->variable_replace( $message, $order );

		foreach ( $this->admin_numbers as $to ) {
			try {
				$response = wc_twilio_sms()->get_api()->send( $to, $message );
			} catch ( Exception $e ) {
				// Set status to error message
				$status = $e->getMessage();
				Logger::log( $status );
			}
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reminder_sms( WC_Order $order ) {
		$this->send_reminder_sms_to_customer( $order );
		$this->send_reminder_sms_to_admin( $order );
	}

	/**
	 * Send reminder SMS to customer
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reminder_sms_to_customer( WC_Order $order ) {
		$message = "Thank You for your  %device_name% %device_model% service order at %prefer_date% %prefer_time%!";
		$message .= " If you need to reschedule please Click here %reschedule_url% or text or call %support_phone%.";

		$to      = $order->get_billing_phone();
		$message = $this->variable_replace( $message, $order );

		try {
			$billing_country = $order->get_meta( 'billing_country', true );
			$response        = wc_twilio_sms()->get_api()->send( $to, $message, $billing_country );
		} catch ( Exception $e ) {
			// Set status to error message
			$status = $e->getMessage();
			Logger::log( $status );
		}
	}

	/**
	 * Send reminder SMS to admin
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function send_reminder_sms_to_admin( WC_Order $order ) {
		$message = "Hi Admin. 24 hours left to arrive at %customer_address% by %prefer_date% %prefer_time% to ";
		$message .= "meet %customer_name%. Be prepared for this %device_issues%.";

		$message = $this->variable_replace( $message, $order );

		foreach ( $this->admin_numbers as $to ) {
			try {
				$response = wc_twilio_sms()->get_api()->send( $to, $message );
			} catch ( Exception $e ) {
				// Set status to error message
				$status = $e->getMessage();
				Logger::log( $status );
			}
		}
	}


	/**
	 * @param string $message
	 * @param WC_Order $order
	 *
	 * @return string
	 * @throws Exception
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

		$reschedule_url = Utils::get_reschedule_url( $order );

		$replacements = array(
			'%order_id%'         => $order->get_id(),
			'%prefer_date%'      => $preferred_date,
			'%prefer_time%'      => $preferred_time,
			'%device_name%'      => $device_title,
			'%device_model%'     => $device_model,
			'%customer_name%'    => $display_name,
			'%device_issues%'    => $device_issues,
			'%customer_address%' => $customer_address,
			'%reschedule_url%'   => $reschedule_url,
			'%support_phone%'    => '561-377-6341',
		);

		return str_replace( array_keys( $replacements ), $replacements, $message );
	}

	/**
	 * Get billing address map url
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_billing_address_map_url( $order ) {
		$address = $order->get_address( 'billing' );
		// Remove name and company before generate the Google Maps URL.
		unset( $address['first_name'], $address['last_name'], $address['company'], $address['email'], $address['phone'] );
		$map_url = 'https://maps.google.com/maps?&q=' . rawurlencode( implode( ', ', $address ) ) . '&z=16';

		$api_key = trim( get_option( 'wc_twilio_sms_firebase_dynamic_links_api_key', '' ) );
		$domain  = trim( get_option( 'wc_twilio_sms_firebase_dynamic_links_domain', '' ) );

		$short_url = new FirebaseDynamicLinks();
		$short_url->set_api_key( $api_key );
		$short_url->set_domain( $domain );

		return $short_url->shorten_url( $map_url );
	}
}
