<?php

namespace Stackonet\Emails;

use Exception;
use Stackonet\Integrations\WooCommerce;
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
	 * @param WC_Order|false $order Order object.
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
	 */
	public function get_content_html() {
		ob_start();
		/** @var WC_Order $order */
		$order           = $this->object;
		$customer_name   = $order->get_formatted_billing_full_name();
		$device_title    = $order->get_meta( '_device_title', true );
		$device_model    = $order->get_meta( '_device_model', true );
		$device_color    = $order->get_meta( '_device_color', true );
		$device_issues   = $order->get_meta( '_device_issues', true );
		$device_issues   = is_array( $device_issues ) ? implode( ', ', $device_issues ) : $device_issues;


		/**
		 * @hooked WC_Emails::email_header() Output the email header
		 */
		do_action( 'woocommerce_email_header', $this->get_heading(), $this );

		echo '<p>';
		echo "Hi Admin!<br>";
		printf( "24 hours left to arrive at customer location to meet %s.<br>", $customer_name );
		echo sprintf( "Device: <strong>%s %s (%s)</strong><br>", $device_title, $device_model, $device_color );
		echo sprintf( "Be prepared for <strong>%s</strong>. ", $device_issues );
		echo '</p>';

		?>
		<table cellspacing="0" cellpadding="0" border="0"
		       style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;">
			<tr>
				<td style="width: 50%;vertical-align: top;"><?php WooCommerce::email_location_box( $order, true ); ?></td>
				<td style="width: 50%;vertical-align: top;"><?php self::email_requested_datetime_box( $order, true ); ?></td>
			</tr>
		</table>
		<?php

		/**
		 * @hooked WC_Emails::email_footer() Output the email footer
		 */
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}

	/**
	 * Get billing address map url
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_billing_address_map_url( $order ) {
		return Utils::shorten_url( $order->get_shipping_address_map_url() );
	}

	/**
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 *
	 * @throws Exception
	 */
	public static function email_requested_datetime_box( $order, $sent_to_admin ) {
		$reschedule_url = Utils::get_reschedule_url( $order );

		$_date     = $order->get_meta( '_reschedule_date_time', true );
		$_date     = is_array( $_date ) ? $_date : [];
		$last_date = end( $_date );

		if ( empty( $last_date['date'] ) || empty( $last_date['time'] ) ) {
			$last_date['date'] = $order->get_meta( '_preferred_service_date', true );
			$last_date['time'] = $order->get_meta( '_preferred_service_time_range', true );
		}

		$assets       = STACKONET_REPAIR_SERVICES_ASSETS;
		?>
		<div class="email-box">
			<h2>
				<img src="<?php echo $assets . '/img/email-icons/clock.png' ?>" width="20" height="20" alt="Clock">
				Date & Time
			</h2>
			<address>
				<?php echo mysql2date( 'l, j M, Y', $last_date['date'] ); ?>
				<br>
				<?php echo $last_date['time']; ?>
			</address>
		</div>
		<?php if ( ! $sent_to_admin ) { ?>
			<p style="margin:10px 0">
				<a href="<?php echo $reschedule_url; ?>" target="_blank">
					<img
						src="<?php echo $assets . '/img/email-icons/calendar.png' ?>" width="16" height="16"
						alt="Calendar">
					Click here to Re-schedule
				</a>
			</p>
		<?php } ?>
		<?php
	}
}
