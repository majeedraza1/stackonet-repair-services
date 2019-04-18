<?php

namespace Stackonet\Supports;

use DateTimeZone;
use Exception;
use Stackonet\Integrations\FirebaseDynamicLinks;
use Stackonet\Models\Settings;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class Utils {

	/**
	 * Return the values from a single column in the input array
	 *
	 * @param array $array A multi-dimensional array (record set) from which to pull a column of values.
	 * @param mixed $column The column of values to return.
	 * @param mixed $index_key [optional] The column to use as the index/keys for the returned array.
	 *
	 * @return array
	 */
	public static function array_column( array $array, $column, $index_key = null ) {
		if ( function_exists( 'array_column' ) ) {
			return array_column( $array, $column, $index_key );
		}

		if ( function_exists( 'wp_list_pluck' ) ) {
			return wp_list_pluck( $array, $column, $index_key );
		}

		$result = array();
		foreach ( $array as $subArray ) {
			if ( is_null( $index_key ) && array_key_exists( $column, $subArray ) ) {
				$result[] = is_object( $subArray ) ? $subArray->$column : $subArray[ $column ];
			} elseif ( array_key_exists( $index_key, $subArray ) ) {
				if ( is_null( $column ) ) {
					$index            = is_object( $subArray ) ? $subArray->$index_key : $subArray[ $index_key ];
					$result[ $index ] = $subArray;
				} elseif ( array_key_exists( $column, $subArray ) ) {
					$index            = is_object( $subArray ) ? $subArray->$index_key : $subArray[ $index_key ];
					$result[ $index ] = is_object( $subArray ) ? $subArray->$column : $subArray[ $column ];
				}
			}
		}

		return $result;
	}

	/**
	 * Shorten URL
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function shorten_url( $url ) {
		$api_key = trim( get_option( 'wc_twilio_sms_firebase_dynamic_links_api_key', '' ) );
		$domain  = trim( get_option( 'wc_twilio_sms_firebase_dynamic_links_domain', '' ) );

		$short_url = new FirebaseDynamicLinks();
		$short_url->set_api_key( $api_key );
		$short_url->set_domain( $domain );

		return $short_url->shorten_url( $url );
	}

	/**
	 * Get reschedule url
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_reschedule_url( $order ) {
		$reschedule_page_id = Settings::get_reschedule_page_id();
		$_reschedule_hash   = $order->get_meta( '_reschedule_hash', true );

		if ( ! is_numeric( $reschedule_page_id ) ) {
			return '';
		}

		$link = get_permalink( $reschedule_page_id );
		if ( ! $link ) {
			return '';
		}

		if ( empty( $_reschedule_hash ) ) {
			$_reschedule_hash = bin2hex( random_bytes( 20 ) );
			update_post_meta( $order->get_id(), '_reschedule_hash', $_reschedule_hash );
		}

		$url = add_query_arg( [ 'order_id' => $order->get_id(), 'token' => $_reschedule_hash, ], $link );

		return self::shorten_url( $url );
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
	public static function get_timezone() {
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


	/**
	 * Generate CSV from array
	 *
	 * @param array $data
	 * @param string $delimiter
	 * @param string $enclosure
	 *
	 * @return string
	 */
	public static function generateCsv( array $data, $delimiter = ',', $enclosure = '"' ) {
		$handle = fopen( 'php://temp', 'r+' );
		foreach ( $data as $line ) {
			fputcsv( $handle, $line, $delimiter, $enclosure );
		}
		rewind( $handle );
		$contents = '';
		while ( ! feof( $handle ) ) {
			$contents .= fread( $handle, 8192 );
		}
		fclose( $handle );

		return $contents;
	}
}
