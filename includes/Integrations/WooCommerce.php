<?php

namespace Stackonet\Integrations;

use Exception;
use Stackonet\Emails\PaymentLinkCustomerEmail;
use Stackonet\Emails\OrderReminderAdminEmail;
use Stackonet\Emails\OrderReminderCustomerEmail;
use Stackonet\Emails\RescheduleAdminEmail;
use Stackonet\Emails\RescheduleCustomerEmail;
use Stackonet\Supports\Utils;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class WooCommerce {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_filter( 'woocommerce_email_classes', array( self::$instance, 'email_classes' ) );

			add_action( 'woocommerce_email_order_details', [ self::$instance, 'location_and_datetime_box' ], 10, 2 );
		}

		return self::$instance;
	}

	/**
	 * Add a custom email to the list of emails WooCommerce should load
	 *
	 * @param array $email_classes available email classes
	 *
	 * @return array filtered available email classes
	 */
	public function email_classes( $email_classes ) {
		$email_classes['admin_reschedule_order']        = new RescheduleAdminEmail();
		$email_classes['customer_reschedule_order']     = new RescheduleCustomerEmail();
		$email_classes['admin_order_reminder_email']    = new OrderReminderAdminEmail();
		$email_classes['customer_order_reminder_email'] = new OrderReminderCustomerEmail();
		$email_classes['customer_order_payment_link']   = new PaymentLinkCustomerEmail();

		return $email_classes;
	}

	/**
	 * Add extra order data
	 *
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 *
	 * @throws Exception
	 */
	public static function location_and_datetime_box( $order, $sent_to_admin ) {
		if ( $sent_to_admin ) {
			?>
			<table cellspacing="0" cellpadding="0" border="0"
			       style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;">
				<tr>
					<td style="width: 50%;vertical-align: top;"><?php self::email_location_box( $order, $sent_to_admin ); ?></td>
					<td style="width: 50%;vertical-align: top;"><?php self::email_requested_datetime_box( $order, $sent_to_admin ); ?></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 */
	public static function email_location_box( $order, $sent_to_admin ) {
		$billing_address = $order->get_formatted_billing_address();
		$map_url         = Utils::shorten_url( $order->get_shipping_address_map_url() );
		$assets          = STACKONET_REPAIR_SERVICES_ASSETS;
		?>
		<div class="email-box">
			<h2>
				<img src="<?php echo $assets . '/img/email-icons/location.png'; ?>" width="20" height="20"
				     alt="Location"/>
				Location
			</h2>
			<address><?php echo $billing_address; ?></address>
		</div>
		<?php if ( $sent_to_admin ) { ?>
			<p style="margin:10px 0">
				<a href="<?php echo $map_url; ?>" target="_blank">
					<img src="<?php echo $assets . '/img/email-icons/map.png' ?>" width="16" height="16" alt="Map">
					View Location on Map
				</a>
			</p>
		<?php }
	}

	/**
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 *
	 * @throws Exception
	 */
	public static function email_requested_datetime_box( $order, $sent_to_admin ) {
		$reschedule_url = Utils::get_reschedule_url( $order );

		$service_date = $order->get_meta( '_preferred_service_date', true );
		$time_range   = $order->get_meta( '_preferred_service_time_range', true );
		$assets       = STACKONET_REPAIR_SERVICES_ASSETS;
		?>
		<div class="email-box">
			<h2>
				<img src="<?php echo $assets . '/img/email-icons/clock.png' ?>" width="20" height="20" alt="Clock">
				Time
			</h2>
			<address>
				<?php echo mysql2date( 'l, j M, Y', $service_date ); ?>
				<br>
				<?php echo $time_range; ?>
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
