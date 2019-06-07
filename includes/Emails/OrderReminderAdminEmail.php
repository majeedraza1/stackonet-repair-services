<?php

namespace Stackonet\Emails;

use Stackonet\Supports\Utils;
use WC_Email;
use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OrderReminderAdminEmail extends WC_Email {

	/**
	 * True when the email notification is sent to customers.
	 *
	 * @var bool
	 */
	protected $customer_email = false;

	/**
	 * Set email defaults
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name
		$this->id = 'admin_order_reminder_email';
		// this is the title in WooCommerce Email settings
		$this->title = 'Admin Reminder Mail';
		// this is the description in WooCommerce email settings
		$this->description = 'Admin reminder order mail send when service date and time are near.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Reminder service date and time';
		$this->subject = 'Reminder service date and time';

		$this->placeholders = array(
			'{site_title}'   => $this->get_blogname(),
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @param int $order_id The order ID.
	 * @param \WC_Order|false $order Order object.
	 */
	public function trigger( $order_id, $order = false ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$this->object = $order;

		$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		$this->placeholders['{order_number}'] = $this->object->get_order_number();

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->recipient = get_option( 'admin_email' );

		// send the email
		$this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
	}

	/**
	 * get_content_html function.
	 *
	 * @return string
	 * @since 0.1
	 */
	public function get_content_html() {
		ob_start();
		/** @var WC_Order $order */
		$order           = $this->object;
		$order_id        = $order->get_id();
		$customer_name   = $order->get_formatted_billing_full_name();
		$billing_address = $order->get_formatted_billing_address();
		$device_title    = $order->get_meta( '_device_title', true );
		$device_model    = $order->get_meta( '_device_model', true );
		$device_issues   = $order->get_meta( '_device_issues', true );
		$device_issues   = is_array( $device_issues ) ? implode( ', ', $device_issues ) : $device_issues;

		$_date     = get_post_meta( $order->get_id(), '_reschedule_date_time', true );
		$_date     = is_array( $_date ) ? $_date : [];
		$last_date = end( $_date );

		$map_url = $this->get_billing_address_map_url( $order );

		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo '<p>';
		echo "Hi Admin!<br>";
		echo sprintf( "24 hours left to arrive at %s by %s %s to meet %s. Be prepared for this %s. ",
			$billing_address, $last_date['date'], $last_date['time'], $customer_name, $device_issues );
		echo '<a href="' . $map_url . '">' . $map_url . '</a>';
		echo '</p>';

		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}

	/**
	 * Get billing address map url
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	private function get_billing_address_map_url( $order ) {
		$address = $order->get_address( 'billing' );
		// Remove name and company before generate the Google Maps URL.
		unset( $address['first_name'], $address['last_name'], $address['company'], $address['email'], $address['phone'] );
		$map_url = 'https://maps.google.com/maps?&q=' . rawurlencode( implode( ', ', $address ) ) . '&z=16';

		return Utils::shorten_url( $map_url );
	}
}
