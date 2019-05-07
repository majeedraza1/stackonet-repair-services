<?php

namespace Stackonet\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeZone;
use Exception;

class Settings {

	/**
	 * @var string
	 */
	private static $option = 'repair_services_settings';

	/**
	 * Default settings
	 *
	 * @var array
	 */
	private static $default = [
		'support_phone'          => '',
		'support_email'          => '',
		'business_address'       => '',
		'reschedule_page_id'     => '',
		'order_reminder_minutes' => '',
		'google_map_key'         => '',
		'dropbox_client_id'      => '',
		'dropbox_client_secret'  => '',
		'dropbox_access_token'   => '',
		'service_times'          => [
			'Monday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Tuesday'   => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Wednesday' => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Thursday'  => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Friday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Saturday'  => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Sunday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
		],
		'holidays_list'          => [ [ 'date' => '', 'note' => '' ] ],
	];

	/**
	 * Get options
	 *
	 * @return array
	 */
	private static function get_option() {
		$option = get_option( self::$option );

		return is_array( $option ) ? wp_parse_args( $option, self::$default ) : [];
	}

	/**
	 * Get map api key
	 *
	 * @return string
	 */
	public static function get_map_api_key() {
		$options = self::get_option();
		$map_key = 'google_map_key';

		return ! empty( $options[ $map_key ] ) ? wp_strip_all_tags( $options[ $map_key ] ) : '';
	}

	/**
	 * Get map api key
	 *
	 * @return string
	 */
	public static function get_dropbox_key() {
		$options = self::get_option();
		$key     = 'dropbox_client_id';

		return ! empty( $options[ $key ] ) ? $options[ $key ] : '';
	}

	/**
	 * Get map api key
	 *
	 * @return string
	 */
	public static function get_dropbox_secret() {
		$options = self::get_option();
		$key     = 'dropbox_client_secret';

		return ! empty( $options[ $key ] ) ? $options[ $key ] : '';
	}

	/**
	 * Get map api key
	 *
	 * @return string
	 */
	public static function get_dropbox_access_token() {
		$options = self::get_option();
		$key     = 'dropbox_access_token';

		return ! empty( $options[ $key ] ) ? $options[ $key ] : '';
	}

	/**
	 * Get reschedule page id
	 *
	 * @return string
	 */
	public static function get_reschedule_page_id() {
		$options = self::get_option();
		$key     = 'reschedule_page_id';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get order reminder minutes
	 *
	 * @return int
	 */
	public static function get_order_reminder_minutes() {
		$options = self::get_option();
		$key     = 'order_reminder_minutes';
		$default = 1440; // 24 hours

		return ! empty( $options[ $key ] ) ? intval( $options[ $key ] ) : $default;
	}

	/**
	 * @return array|mixed
	 * @throws Exception
	 */
	public static function get_service_dates_ranges() {
		$key     = 'holidays_list';
		$options = self::get_option();

		$holidays = ! empty( $options[ $key ] ) ? $options[ $key ] : [];
		$off_days = array_filter( wp_list_pluck( $holidays, 'date' ) );

		$days     = [];
		$date     = new DateTime();
		$timezone = get_option( 'timezone_string' );
		if ( in_array( $timezone, DateTimeZone::listIdentifiers() ) ) {
			$date->setTimezone( new DateTimeZone( $timezone ) );
		}
		/** @var DateTime[] $period */
		$period = new DatePeriod( $date, new DateInterval( 'P1D' ), new DateTime( '+ 10 days' ) );
		foreach ( $period as $day ) {
			$_date      = $day->format( 'Y-m-d' );
			$is_holiday = in_array( $_date, $off_days );
			$index      = array_search( $_date, $off_days );
			$has_note   = $is_holiday && ! empty( $holidays[ $index ]['note'] );

			$days[] = [
				'date'    => $_date,
				'day'     => $day->format( 'l' ),
				'holiday' => $is_holiday,
				'note'    => $has_note ? wp_strip_all_tags( $holidays[ $index ]['note'] ) : '',
			];
		}

		return $days;
	}

	/**
	 * Get service times
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_service_times_ranges() {
		$key     = 'service_times';
		$options = self::get_option();

		$service_times = ! empty( $options[ $key ] ) ? $options[ $key ] : [];
		$_times        = [];
		foreach ( $service_times as $day_name => $service_time ) {
			$start = date( 'Y-m-d', time() ) . ' ' . $service_time['start_time'];
			$end   = date( 'Y-m-d', time() ) . ' ' . $service_time['end_time'];

			$date1 = new DateTime( $start );
			$date2 = new DateTime( $end );

			$interval = new DateInterval( 'PT1H' );
			/** @var DateTime[] $period */
			$period = new DatePeriod( $date1, $interval, $date2 );

			foreach ( $period as $day ) {
				$_h1 = $day->format( 'ha' );
				$day->modify( '+ 1 hour' );
				$_times[ $day_name ][] = sprintf( '%s - %s', $_h1, $day->format( 'ha' ) );
			}
		}

		return $_times;
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::get_option();
	}

	/**
	 * Update settings
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function update( array $data ) {
		$_data = [];
		foreach ( self::$default as $options_key => $default ) {
			$_data[ $options_key ] = isset( $data[ $options_key ] ) ? $data[ $options_key ] : $default;
		}
		update_option( self::$option, $_data );

		return $data;
	}
}
