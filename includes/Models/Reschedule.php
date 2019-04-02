<?php

namespace Stackonet\Models;

use DateTime;
use Exception;
use Stackonet\Abstracts\BackgroundProcess;
use Stackonet\Integrations\Twilio;
use Stackonet\RescheduleAdminEmail;
use Stackonet\RescheduleCustomerEmail;
use WC_Order;

class Reschedule extends BackgroundProcess {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'reschedule_background_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function task( $item ) {
		$order = wc_get_order( $item['order_id'] );
		self::process( $order );

		return false;
	}

	/**
	 * Process a reschedule
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public static function process( WC_Order $order ) {
		self::send_sms_to_customer( $order );
		self::send_sms_to_admin( $order );
		self::send_mail_to_admin( $order );
		self::send_mail_to_customer( $order );
	}

	/**
	 * @param WC_Order $order
	 * @param bool $include_original
	 *
	 * @return array|mixed
	 */
	public static function get_service_date( WC_Order $order, $include_original = true ) {
		$service_date       = $order->get_meta( '_preferred_service_date', true );
		$service_time_range = $order->get_meta( '_preferred_service_time_range', true );

		$data = [
			'date'       => $service_date,
			'time'       => $service_time_range,
			'user'       => $order->get_customer_id(),
			'created_at' => $order->get_date_created()->format( DateTime::ISO8601 ),
			'created_by' => 'customer',
		];

		$_reschedule_date = $order->get_meta( '_reschedule_date_time', true );
		$_reschedule_date = is_array( $_reschedule_date ) ? $_reschedule_date : [];

		if ( $include_original ) {
			array_unshift( $_reschedule_date, $data );
		}

		return $_reschedule_date;
	}

	/**
	 * Update reschedule service date
	 *
	 * @param WC_Order $order
	 * @param array $data
	 */
	public static function update_service_date( WC_Order $order, array $data ) {
		$dates   = self::get_service_date( $order, false );
		$dates[] = $data;

		update_post_meta( $order->get_id(), '_reschedule_date_time', $dates );

		do_action( 'save_order_reschedule', $order, $data );
	}

	/**
	 * Send reschedule sms to customer
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public static function send_sms_to_customer( WC_Order $order ) {
		$twilio = new Twilio();
		$twilio->send_reschedule_sms_to_customer( $order );
	}

	/**
	 * Send reschedule sms to admin
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public static function send_sms_to_admin( WC_Order $order ) {
		$twilio = new Twilio();
		$twilio->send_reschedule_sms_to_admin( $order );
	}

	/**
	 * Send reschedule mail to admin
	 *
	 * @param WC_Order $order
	 */
	public static function send_mail_to_admin( WC_Order $order ) {
		$email = wc()->mailer()->get_emails();
		/** @var RescheduleAdminEmail $admin_email */
		$admin_email = $email['admin_reschedule_order'];
		$admin_email->trigger( $order->get_id(), $order );
	}

	/**
	 * Send reschedule mail to customer
	 *
	 * @param WC_Order $order
	 */
	public static function send_mail_to_customer( WC_Order $order ) {
		$email = wc()->mailer()->get_emails();
		/** @var RescheduleCustomerEmail $user_email */
		$user_email = $email['customer_reschedule_order'];
		$user_email->trigger( $order->get_id(), $order );
	}
}
