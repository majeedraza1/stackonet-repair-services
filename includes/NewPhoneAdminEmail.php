<?php

namespace Stackonet;

use Stackonet\Models\Phone;
use WC_Email;
use WC_Order;

class NewPhoneAdminEmail extends WC_Email {
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
		$this->id = 'admin_new_phone_email';
		// this is the title in WooCommerce Email settings
		$this->title = 'Admin New Phone Email';
		// this is the description in WooCommerce email settings
		$this->description = 'Admin email when creating new phone by manager.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'New Phone';
		$this->subject = 'New Phone';

		$this->placeholders = array(
			'{site_title}' => $this->get_blogname(),
		);

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @param Phone $phone Order object.
	 */
	public function trigger( $phone ) {
		if ( ! $phone instanceof Phone ) {
			return;
		}

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

		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo 'Test content';


		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}
}
