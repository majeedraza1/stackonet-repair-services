<?php

namespace Stackonet\Emails;

use Exception;
use Stackonet\Models\Settings;
use Stackonet\Supports\Utils;
use WC_Email;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class PaymentLinkCustomerEmail extends WC_Email {

	/**
	 * True when the email notification is sent to customers.
	 *
	 * @var bool
	 */
	protected $customer_email = true;

	/**
	 * @var string|null
	 */
	protected $payment_url = null;

	/**
	 * Set email defaults
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name
		$this->id = 'customer_order_payment_link_email';
		// this is the title in WooCommerce Email settings
		$this->title = 'Customer Payment Link Mail';
		// this is the description in WooCommerce email settings
		$this->description = 'Customer payment link order mail send when you manually choose to send mail.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Payment request for repair services.';
		$this->subject = 'Payment request for repair services.';

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
	 * @param WC_Order|false $order Order object.
	 * @param null|string $payment_url
	 */
	public function trigger( $order_id, $order = false, $payment_url = null ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$this->object = $order;

		if ( ! empty( $payment_url ) ) {
			$this->payment_url = $payment_url;
		}

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
	 * @throws Exception
	 */
	public function get_content_html() {
		ob_start();

		/** @var WC_Order $order */
		$order         = $this->object;
		$customer_name = $order->get_formatted_billing_full_name();

		if ( empty( $this->payment_url ) ) {
			$payment_page_id = Settings::get_payment_page_id();
			$page_url        = get_permalink( $payment_page_id );
			$payment_url     = add_query_arg( [
				'order' => $order->get_id(),
				'token' => $order->get_meta( '_reschedule_hash', true ),
			], $page_url );

			$this->payment_url = Utils::shorten_url( $payment_url );
		}

		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo '<p style="margin: 50px;font-size: 18px;line-height: 150%">';
		echo sprintf( "Hi %s!<br> Thanks for choosing us. We have generated a Payment Link for the order #%s", $customer_name, $order->get_id() );
		echo '<a href="' . $this->payment_url . '" class="button" target="_blank" style="display: inline-block; width: 100%; min-height: 20px; padding: 10px; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px; text-align: center; text-decoration: none; -webkit-text-size-adjust: none;background-color: #f9a73b;">
			Click Here to Pay Now 
        </a>';
		echo '</p>';

		/*
		 * @hooked WC_Emails::order_details() Shows the order details table.
		 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
		 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
		 * @since 2.5.0
		 */
		do_action( 'woocommerce_email_order_details', $order, false, false, $this );

		/*
		 * @hooked WC_Emails::order_meta() Shows order meta data.
		 */
		do_action( 'woocommerce_email_order_meta', $order, false, false, $this );

		/*
		 * @hooked WC_Emails::customer_details() Shows customer details
		 * @hooked WC_Emails::email_address() Shows email address
		 */
		do_action( 'woocommerce_email_customer_details', $order, false, false, $this );

		echo '<p>If you have any questions whatsoever, please call us at 561-FIX-ITFL (561-349-4835) or send mail at <a href="mailto:support@phonerepairsasap.com">support@phonerepairsasap.com</a> and We’d be happy to clarify them.</p>';

		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}
}
