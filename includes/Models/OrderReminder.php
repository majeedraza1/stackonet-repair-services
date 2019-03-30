<?php

namespace Stackonet\Models;

use DateTime;
use DateTimeZone;
use Exception;
use Stackonet\Abstracts\BackgroundProcess;
use Stackonet\Integrations\Twilio;
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
	 * @throws Exception
	 */
	public function init() {
		$reminder_data = self::get_orders_reminder_data();
		foreach ( $reminder_data as $item ) {
			if ( $item['can_send_sms'] ) {
				$reminder = stackonet_repair_services()->order_reminder();
				$reminder->push_to_queue( $item );
				$reminder->save();
				$reminder->dispatch();
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
	 */
	protected function task( $item ) {
		$order = wc_get_order( $item['order_id'] );
		self::process( $order );

		return false;
	}

	/**
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function get_orders_reminder_data() {
		$data = get_transient( 'get_orders_reminder_data' );
		if ( false === $data ) {
			$query = new WC_Order_Query();
			$query->set( 'limit', - 1 );
			$query->set( 'status', [ 'wc-processing' ] );
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			/** @var WC_Order[] $orders */
			$orders = $query->get_orders();

			$data = [];
			foreach ( $orders as $order ) {
				$data[ $order->get_id() ] = self::get_order_reminder_data( $order );
			}

			set_transient( 'get_orders_reminder_data', $data, DAY_IN_SECONDS );
		}

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
		$dateTimeNow      = new DateTime( 'now', $timezone );
		$serviceTime      = new DateTime( $date . ' ' . $_time, $timezone );
		$reminderTime     = clone $serviceTime;
		$reminderTime->modify( '-' . $reminder_minutes . ' minutes' );
		$sendSMS = ( $dateTimeNow <= $reminderTime ) && ( $serviceTime > $reminderTime );

		return [
			'order_id'      => $order->get_id(),
			'order_status'  => $order->get_status(),
			'service_time'  => $serviceTime->format( 'Y-m-d H:i:s' ),
			'reminder_time' => $reminderTime->format( 'Y-m-d H:i:s' ),
			'can_send_sms'  => ( $sendSMS && ! $is_sms_sent ),
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
