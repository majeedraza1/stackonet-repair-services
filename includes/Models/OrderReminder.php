<?php

namespace Stackonet\Models;

use WC_Order;

class OrderReminder {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'order_reminder_background_process';

	/**
	 * @param WC_Order $order
	 */
	public static function process( WC_Order $order ) {
		$is_sms_sent      = $order->get_meta( '_is_order_reminder_send', true );
		$reminder_minutes = Settings::get_order_reminder_minutes();
		$service_dates    = Reschedule::get_service_date( $order );
		$service_date     = is_array( $service_dates ) ? end( $service_dates ) : [];

		$date = $service_date['date'];
		$time = $service_date['time'];


		var_dump( [ $date, $time, $reminder_minutes, $service_date ] );
	}
}
