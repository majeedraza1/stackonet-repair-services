<?php

namespace Stackonet;

use Stackonet\Supports\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RescheduleCustomerEmail extends \WC_Email {

	/**
	 * True when the email notification is sent to customers.
	 *
	 * @var bool
	 */
	protected $customer_email = true;

	/**
	 * Set email defaults
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name
		$this->id = 'customer_reschedule_order';
		// this is the title in WooCommerce Email settings
		$this->title = 'Admin Reschedule Order';
		// this is the description in WooCommerce email settings
		$this->description = 'Admin reschedule order mail send when admin or customer reschedule date and time.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Appointment Date and Time have been rescheduled';
		$this->subject = 'Appointment Date and Time have been rescheduled';

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
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function get_content_html() {
		ob_start();

		/** @var \WC_Order $order */
		$order         = $this->object;
		$order_id      = $order->get_id();
		$customer_name = $order->get_formatted_billing_full_name();

		$_date          = get_post_meta( $order->get_id(), '_reschedule_date_time', true );
		$_date          = is_array( $_date ) ? $_date : [];
		$last_date      = end( $_date );
		$reschedule_url = Utils::get_reschedule_url( $order );

		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo '<p>';
		echo sprintf( "Thank you %s Your Appointment has been rescheduled. ", $customer_name );
		echo sprintf( "We will be arriving at your place by %s %s. ", $last_date['date'], $last_date['time'] );
		echo "If you wish to reschedule appointment Click Here ";
		echo '<a href="' . $reschedule_url . '">' . $reschedule_url . '</a>';
		echo '</p>';

		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}

}
