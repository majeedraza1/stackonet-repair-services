<?php

namespace Stackonet;

use Stackonet\Models\Device;
use Stackonet\Models\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewDeviceRepairsOrderEmail extends \WC_Email {

	protected $customer_email = true;

	/**
	 * Set email defaults
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name
		$this->id = 'new_repair_order';
		// this is the title in WooCommerce Email settings
		$this->title = 'New repair order';
		// this is the description in WooCommerce email settings
		$this->description = 'New repair order emails are sent to chosen recipient(s) when a new repair order is received.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'We\'ve received your appointment request';
		$this->subject = 'We\'ve received your appointment request';

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

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$this->object = $order;

		$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		$this->placeholders['{order_number}'] = $this->object->get_order_number();

		if ( ! $this->is_enabled() ) {
			return;
		}

		$billing_email = $this->object->get_billing_email();
		if ( ! is_email( $billing_email ) ) {
			return;
		}

		$this->recipient = $billing_email;

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
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		/** @var \WC_Order $order */
		$order = $this->object;
		$items = $order->get_items();
		$items = array_values( $items );
		/** @var \WC_Order_Item $first_item */
		$first_item   = isset( $items[0] ) ? $items[0] : false;
		$device_id    = $first_item->get_meta( '_device_id', true );
		$device_title = $first_item->get_meta( '_device_title', true );
		$device_model = $first_item->get_meta( '_device_model', true );
		$device_color = $first_item->get_meta( '_device_color', true );
		$device       = Device::get_device( $device_id );
		$title        = sprintf( "%s %s, %s", $device_title, $device_model, $device_color );
		$order_total  = wc_price( $order->get_total() );

		$service_date   = $order->get_meta( '_preferred_service_date', true );
		$time_range     = $order->get_meta( '_preferred_service_time_range', true );
		$requested_time = sprintf( "%s, %s", mysql2date( 'l j M', $service_date ), $time_range );

		$fname = $order->get_billing_first_name();
		$lname = $order->get_billing_last_name();

		$name = '';
		if ( ! empty( $fname ) && ! empty( $lname ) ) {
			$name = sprintf( "%s %s", $fname, $lname );
		} elseif ( ! empty( $lname ) ) {
			$name = $lname;
		} elseif ( ! empty( $fname ) ) {
			$name = $fname;
		}

		$settings = Settings::get_settings();

		$privacy_url   = get_privacy_policy_url();
		$terms_url     = '';
		$terms_page_id = wc_terms_and_conditions_page_id();
		if ( ! empty( $terms_page_id ) && get_post_status( $terms_page_id ) === 'publish' ) {
			$terms_url = (string) get_permalink( $terms_page_id );
		}

		include STACKONET_REPAIR_SERVICES_PATH . '/templates/emails/new-repair-order.php';

		return ob_get_clean();
	}
}
