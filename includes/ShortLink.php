<?php

namespace Stackonet;

use Exception;

class ShortLink {

	/**
	 * Database Table name
	 *
	 * @var string
	 */
	protected static $table = 'url_short_links';

	/**
	 * @var string
	 */
	protected static $chars = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";

	/**
	 * @var bool
	 */
	protected static $checkUrlExists = true;

	/**
	 * Create database table
	 */
	public static function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `long_url` LONGTEXT DEFAULT NULL,
                `short_code` varchar(6) DEFAULT NULL,
                `counter` bigint(20) unsigned NOT NULL DEFAULT '0',
                `created_by` bigint(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY short_code (short_code)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}

	/**
	 * @param string $url
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public function urlToShortCode( $url ) {
		if ( empty( $url ) ) {
			throw new Exception( "No URL was supplied." );
		}

		if ( filter_var( $url, FILTER_VALIDATE_URL ) == false ) {
			throw new Exception( "URL does not have a valid format." );
		}

		if ( self::$checkUrlExists ) {
			if ( ! $this->verifyUrlExists( $url ) ) {
				throw new Exception( "URL does not appear to exist." );
			}
		}

		$shortCode = $this->urlExistsInDb( $url );
		if ( $shortCode == false ) {
			$shortCode = $this->createShortCode( $url );
		}

		return $shortCode;
	}

	/**
	 * Check if URL exists
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function verifyUrlExists( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_exec( $ch );
		$response = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return ( ! empty( $response ) && $response != 404 );
	}

	/**
	 * Check if URL exists in Database
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function urlExistsInDb( $url ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;
		$result     = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE long_url = %s", $url ),
			ARRAY_A
		);

		return empty( $result ) ? false : $result["short_code"];
	}

	/**
	 * @param $url
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function createShortCode( $url ) {
		$id        = $this->insertUrlInDb( $url );
		$shortCode = $this->convertIntToShortCode( $id );
		$this->insertShortCodeInDb( $id, $shortCode );

		return $shortCode;
	}

	/**
	 * Insert URL into Database
	 *
	 * @param string $url
	 *
	 * @return int
	 */
	protected function insertUrlInDb( $url ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;

		$wpdb->insert( $table_name, [
			'long_url'   => $url,
			'created_by' => get_current_user_id(),
			'created_at' => current_time( 'mysql' ),
		], [ '%s', '%d', '%s' ] );

		return $wpdb->insert_id;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function convertIntToShortCode( $id ) {
		$id = intval( $id );
		if ( $id < 1 ) {
			throw new Exception( "The ID is not a valid integer" );
		}

		$length = strlen( self::$chars );
		// make sure length of available characters is at
		// least a reasonable minimum - there should be at
		// least 10 characters
		if ( $length < 10 ) {
			throw new Exception( "Length of chars is too small" );
		}

		$code = "";
		while ( $id > $length - 1 ) {
			// determine the value of the next higher character
			// in the short code should be and prepend
			$code = self::$chars[ fmod( $id, $length ) ] . $code;
			// reset $id to remaining value to be converted
			$id = floor( $id / $length );
		}

		// remaining value of $id is less than the length of
		// self::$chars
		$code = self::$chars[ $id ] . $code;

		return $code;
	}

	/**
	 * Insert short code into database
	 *
	 * @param int $id
	 * @param string $code
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function insertShortCodeInDb( $id, $code ) {
		if ( $id == null || $code == null ) {
			throw new Exception( "Input parameter(s) invalid." );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;
		$result     = $wpdb->update( $table_name, [ 'short_code' => $code, ], [ 'id' => $id ] );

		if ( $result === false ) {
			throw new Exception( "Row was not updated with short code." );
		}

		return true;
	}

	/**
	 * @param string $code
	 * @param bool $increment
	 *
	 * @return string
	 * @throws Exception
	 */
	public function shortCodeToUrl( $code, $increment = true ) {
		if ( empty( $code ) ) {
			throw new Exception( "No short code was supplied." );
		}

		if ( $this->validateShortCode( $code ) == false ) {
			throw new Exception( "Short code does not have a valid format." );
		}

		$urlRow = $this->getUrlFromDb( $code );
		if ( empty( $urlRow ) ) {
			throw new Exception( "Short code does not appear to exist." );
		}

		if ( $increment == true ) {
			$this->incrementCounter( $urlRow["id"] );
		}

		return $urlRow["long_url"];
	}

	/**
	 * Check if short code valid
	 *
	 * @param string $code
	 *
	 * @return false|int
	 */
	protected function validateShortCode( $code ) {
		return preg_match( "|[" . self::$chars . "]+|", $code );
	}

	/**
	 * @param string $code
	 *
	 * @return array|bool
	 */
	protected function getUrlFromDb( $code ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;
		$result     = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE short_code = %s", $code ),
			ARRAY_A
		);

		return empty( $result ) ? false : $result;
	}

	/**
	 * Increment counter
	 *
	 * @param int $id
	 */
	protected function incrementCounter( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;
		$result     = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( isset( $result['counter'] ) && is_numeric( $result['counter'] ) ) {
			$wpdb->update( $table_name, [ 'counter' => ( $result['counter'] + 1 ), ], [ 'id' => $id ] );
		}
	}
}
