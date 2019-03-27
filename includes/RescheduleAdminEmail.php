<?php

namespace Stackonet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RescheduleAdminEmail extends \WC_Email {
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
		$this->id = 'admin_reschedule_order';
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

		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo "NEW APPOINTMENT CHANGES [order id]: [Customer name] has ";
		echo "rescheduled a appointment [Device][issue] at [address]. Please arrive by [time]. [Map Link]";

		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}
}
