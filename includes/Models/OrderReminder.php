<?php

namespace Stackonet\Models;

use DateTime;
use DateTimeZone;
use Exception;
use Stackonet\Abstracts\BackgroundProcess;
use Stackonet\Integrations\Twilio;
use Stackonet\Emails\OrderReminderAdminEmail;
use Stackonet\Emails\OrderReminderCustomerEmail;
use Stackonet\Supports\Logger;
use WC_Order;
use WC_Order_Query;

class OrderReminder extends BackgroundProcess {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'order_reminder_background_process';

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * @return OrderReminder
	 * @throws Exception
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'init', [ self::$instance, 'init_queue' ] );
			add_action( 'stackonet_order_created', [ self::$instance, 'add_new_order' ] );
			add_action( 'save_order_reschedule', [ self::$instance, 'save_order_reschedule' ] );
			add_action( 'delete_post', [ self::$instance, 'delete_order' ] );
		}

		return self::$instance;
	}

	/**
	 * Initiate queue
	 *
	 * @throws Exception
	 */
	public function init_queue() {
		$reminder_data = array_filter( self::get_orders_reminder_data() );
		if ( count( $reminder_data ) < 1 ) {
			return;
		}
		$timezone         = self::get_blog_timezone();
		$reminder_minutes = Settings::get_order_reminder_minutes();

		foreach ( $reminder_data as $item ) {
			// If SMS already sent, then exit
			if ( isset( $item['is_sms_sent'] ) && $item['is_sms_sent'] ) {
				continue;
			}
			$dateTimeNow    = new DateTime( 'now' );
			$created_at     = new DateTime( $item['created_at'] );
			$serviceTime    = new DateTime( $item['service_time'] );
			$reminderTime   = new DateTime( $item['reminder_time'] );
			$interval       = ( $serviceTime->getTimestamp() - $created_at->getTimestamp() );
			$interval_hours = absint( $interval / 60 );
			$sendSMS        = ( $dateTimeNow <= $reminderTime ) && ( $serviceTime > $reminderTime ) && ( $interval_hours > $reminder_minutes );

			if ( ! $sendSMS ) {
				continue;
			}

			$this->update_transient( $item['order_id'], [
				'order_id'      => $item['order_id'],
				'order_status'  => $item['order_status'],
				'created_at'    => $item['created_at'],
				'service_time'  => $item['service_time'],
				'reminder_time' => $item['reminder_time'],
				'is_sms_sent'   => true,
			] );
			update_post_meta( $item['order_id'], '_is_order_reminder_send', 1 );

			$reminder = stackonet_repair_services()->order_reminder();
			$reminder->push_to_queue( $item );
			$reminder->save();
			$reminder->dispatch();
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function add_new_order( WC_Order $order ) {
		$data                     = self::get_orders_reminder_data();
		$data[ $order->get_id() ] = self::get_order_reminder_data( $order );

		delete_transient( 'get_orders_reminder_data' );
		set_transient( 'get_orders_reminder_data', $data );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function save_order_reschedule( WC_Order $order ) {
		$order_id          = $order->get_id();
		$data              = self::get_orders_reminder_data();
		$data[ $order_id ] = self::get_order_reminder_data( $order );

		delete_transient( 'get_orders_reminder_data' );
		set_transient( 'get_orders_reminder_data', $data );
	}

	/**
	 * Update transient
	 *
	 * @param int $order_id
	 * @param array $new_data
	 */
	public function update_transient( $order_id, array $new_data ) {
		$data = get_transient( 'get_orders_reminder_data' );
		$data = is_array( $data ) ? $data : [];

		$data[ $order_id ] = $new_data;
		delete_transient( 'get_orders_reminder_data' );
		set_transient( 'get_orders_reminder_data', $data );
	}

	/**
	 * Create Transient
	 *
	 * @param int $post_id
	 *
	 * @throws Exception
	 */
	public function delete_order( $post_id ) {
		if ( get_post_type( $post_id ) == 'shop_order' ) {
			$data = self::get_orders_reminder_data();
			if ( isset( $data[ $post_id ] ) ) {
				unset( $data[ $post_id ] );
				delete_transient( 'get_orders_reminder_data' );
				set_transient( 'get_orders_reminder_data', $data );
			}
		}
	}

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
		if ( $order instanceof WC_Order ) {
			self::process( $order );
		}

		return false;
	}

	/**
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function get_orders_reminder_data() {
		$data = get_transient( 'get_orders_reminder_data' );
		if ( ! empty( $data ) && is_array( $data ) ) {
			return $data;
		}

		return [];

		$query = new WC_Order_Query();
		$query->set( 'limit', - 1 );
		$query->set( 'status', [ 'wc-processing' ] );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		/** @var WC_Order[] $orders */
		$orders = $query->get_orders();
		$data   = [];
		foreach ( $orders as $order ) {
			$data[ $order->get_id() ] = self::get_order_reminder_data( $order );
		}

		set_transient( 'get_orders_reminder_data', $data );

		return $data;
	}

	/**
	 * Get order reminder data
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_order_reminder_data( WC_Order $order ) {
		$is_sms_sent      = (bool) $order->get_meta( '_is_order_reminder_send', true );
		$reminder_minutes = Settings::get_order_reminder_minutes();
		$service_dates    = Reschedule::get_service_date( $order );
		$service_date     = is_array( $service_dates ) ? end( $service_dates ) : [];
		$date             = $service_date['date'];
		$time             = $service_date['time'];
		$_time            = explode( ' - ', $time );
		$_time            = isset( $_time[0] ) ? $_time[0] : '';
		$timezone         = self::get_blog_timezone();
		$serviceTime      = new DateTime( $date . ' ' . $_time, $timezone );
		$reminderTime     = clone $serviceTime;
		$reminderTime->modify( '-' . $reminder_minutes . ' minutes' );

		return [
			'order_id'      => $order->get_id(),
			'order_status'  => $order->get_status(),
			'created_at'    => $order->get_date_created()->format( DateTime::ISO8601 ),
			'service_time'  => $serviceTime->format( DateTime::ISO8601 ),
			'reminder_time' => $reminderTime->format( DateTime::ISO8601 ),
			'is_sms_sent'   => $is_sms_sent,
		];
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public static function process( WC_Order $order ) {
		$twilio = new Twilio();
		$twilio->send_reminder_sms( $order );
		self::send_mail_to_admin( $order );
		self::send_mail_to_customer( $order );
	}

	/**
	 * Send reschedule mail to admin
	 *
	 * @param WC_Order $order
	 */
	public static function send_mail_to_admin( WC_Order $order ) {
		$email = wc()->mailer()->get_emails();
		/** @var OrderReminderAdminEmail $admin_email */
		$admin_email = $email['admin_order_reminder_email'];
		$admin_email->trigger( $order->get_id(), $order );
	}

	/**
	 * Send reschedule mail to customer
	 *
	 * @param WC_Order $order
	 */
	public static function send_mail_to_customer( WC_Order $order ) {
		$email = wc()->mailer()->get_emails();
		/** @var OrderReminderCustomerEmail $user_email */
		$user_email = $email['customer_order_reminder_email'];
		$user_email->trigger( $order->get_id(), $order );
	}

	/**
	 *  Returns the blog timezone
	 *
	 * Gets timezone settings from the db. If a timezone identifier is used just turns
	 * it into a DateTimeZone. If an offset is used, it tries to find a suitable timezone.
	 * If all else fails it uses UTC.
	 *
	 * @return DateTimeZone The blog timezone
	 */
	public static function get_blog_timezone() {
		$timezone_string = get_option( 'timezone_string' );
		$offset          = get_option( 'gmt_offset' );

		//Manual offset...
		//@see http://us.php.net/manual/en/timezones.others.php
		//@see https://bugs.php.net/bug.php?id=45543
		//@see https://bugs.php.net/bug.php?id=45528
		//IANA timezone database that provides PHP's timezone support uses POSIX (i.e. reversed) style signs
		if ( empty( $timezone_string ) && 0 != $offset && floor( $offset ) == $offset ) {
			$offset_st       = $offset > 0 ? "-$offset" : '+' . absint( $offset );
			$timezone_string = 'Etc/GMT' . $offset_st;
		}

		//Issue with the timezone selected, set to 'UTC'
		if ( empty( $timezone_string ) ) {
			$timezone_string = 'UTC';
		}

		$timezone = new DateTimeZone( $timezone_string );

		return $timezone;
	}
}
