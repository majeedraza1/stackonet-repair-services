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
		'support_phone'                 => '',
		'support_email'                 => '',
		'business_address'              => '',
		'reschedule_page_id'            => '',
		'payment_page_id'               => '',
		'terms_and_conditions_page_id'  => '',
		'order_reminder_minutes'        => '',
		'ipdata_api_key'                => '',
		'google_map_key'                => '',
		'dropbox_client_id'             => '',
		'dropbox_client_secret'         => '',
		'dropbox_access_token'          => '',
		'minimum_time_difference'       => '',
		'square_payment_application_id' => '',
		'square_payment_access_token'   => '',
		'square_payment_location_id'    => '',
		'payment_thank_you_page_id'     => '',
		'service_times'                 => [
			'Monday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Tuesday'   => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Wednesday' => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Thursday'  => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Friday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Saturday'  => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
			'Sunday'    => [ 'start_time' => '09:00', 'end_time' => '22:00' ],
		],
		'holidays_list'                 => [ [ 'date' => '', 'note' => '' ] ],
	];

	/**
	 * Get options
	 *
	 * @return array
	 */
	public static function get_option() {
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
	public static function get_ipdata_api_key() {
		$options = self::get_option();
		$map_key = 'ipdata_api_key';

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
	 * Get payment page id
	 *
	 * @return string
	 */
	public static function get_payment_page_id() {
		$options = self::get_option();
		$key     = 'payment_page_id';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get square payment application id
	 *
	 * @return string
	 */
	public static function get_square_payment_application_id() {
		$options = self::get_option();
		$key     = 'square_payment_application_id';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get square payment access token
	 *
	 * @return string
	 */
	public static function get_square_payment_access_token() {
		$options = self::get_option();
		$key     = 'square_payment_access_token';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get square payment location id
	 *
	 * @return string
	 */
	public static function get_square_payment_location_id() {
		$options = self::get_option();
		$key     = 'square_payment_location_id';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get payment thank you page id
	 *
	 * @return string
	 */
	public static function get_payment_thank_you_page_id() {
		$options = self::get_option();
		$key     = 'payment_thank_you_page_id';

		return ! empty( $options[ $key ] ) ? wp_strip_all_tags( $options[ $key ] ) : '';
	}

	/**
	 * Get terms and conditions page id
	 *
	 * @return string
	 */
	public static function get_terms_and_conditions_page_id() {
		$options = self::get_option();
		$key     = 'terms_and_conditions_page_id';

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
	 * Get today times ranges
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_today_times_ranges() {
		$current_time     = current_time( 'timestamp' );
		$options          = self::get_option();
		$difference       = ! empty( $options['minimum_time_difference'] ) ? intval( $options['minimum_time_difference'] ) : 0;
		$start_time       = $current_time + ( $difference * 60 );
		$round_start_time = round( $start_time / 3600 ) * 3600;

		$_start_time = intval( date( 'Hi', $round_start_time ) );

		$time_ranges = [];

		if ( $_start_time <= 900 ) {
			$time_ranges[] = '09am - 12pm';
		}

		if ( $_start_time > 900 && $_start_time <= 1100 ) {
			$time_ranges[] = date( 'ha', $round_start_time ) . ' - 12pm';
		}

		if ( $_start_time <= 1200 ) {
			$time_ranges[] = '12pm - 3pm';
		}

		if ( $_start_time > 1200 && $_start_time <= 1400 ) {
			$time_ranges[] = date( 'ha', $round_start_time ) . ' - 3pm';
		}

		if ( $_start_time <= 1500 ) {
			$time_ranges[] = '3pm - 6pm';
		}

		if ( $_start_time > 1500 && $_start_time <= 1700 ) {
			$time_ranges[] = date( 'ha', $round_start_time ) . ' - 6pm';
		}

		if ( $_start_time <= 1800 ) {
			$time_ranges[] = '6pm - 9pm';
		}

		return $time_ranges;
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

			$interval = new DateInterval( 'PT3H' );
			/** @var DateTime[] $period */
			$period = new DatePeriod( $date1, $interval, $date2 );

//			$time_ranges         = [ '09am - 12pm', '12pm - 3pm', '3pm - 6pm', '6pm - 9pm' ];
//			$_times[ $day_name ] = $time_ranges;

			foreach ( $period as $day ) {
				$_h1 = $day->format( 'ha' );
				$day->modify( '+ 3 hour' );
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
