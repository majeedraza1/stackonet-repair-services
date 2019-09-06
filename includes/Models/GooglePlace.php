<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Supports\Logger;

defined( 'ABSPATH' ) || exit;

class GooglePlace extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_google_place';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'                         => '',
		'place_id'                   => '',
		'latitude'                   => '',
		'longitude'                  => '',
		'address_components'         => '',
		'formatted_address'          => '',
		'formatted_phone_number'     => '',
		'geometry'                   => '',
		'icon'                       => '',
		'international_phone_number' => '',
		'name'                       => '',
		'rating'                     => '',
		'reference'                  => '',
		'reviews'                    => '',
		'types'                      => '',
		'url'                        => '',
		'utc_offset'                 => '',
		'vicinity'                   => '',
		'website'                    => '',
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [
		'%d',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%d',
		'%s',
		'%s',
	];

	/**
	 * Get place id
	 *
	 * @return int
	 */
	public function get_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get place by place ID
	 *
	 * @param string $place_id
	 *
	 * @return bool|GooglePlace
	 */
	public static function get_place_from_place_id( $place_id ) {
		global $wpdb;
		$self  = new static;
		$table = $wpdb->prefix . $self->table;

		$sql  = $wpdb->prepare( "SELECT * FROM {$table} WHERE place_id = %s", $place_id );
		$item = $wpdb->get_row( $sql, ARRAY_A );

		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * @param int|float $latitude
	 * @param int|float $longitude
	 *
	 * @return array|mixed|object
	 */
	public static function get_address_from_lat_lng( $latitude, $longitude ) {
		global $wpdb;
		$self  = new static;
		$table = $wpdb->prefix . $self->table;

		$sql  = $wpdb->prepare( "SELECT * FROM {$table} WHERE latitude = %s AND longitude = %s", $latitude, $longitude );
		$item = $wpdb->get_row( $sql, ARRAY_A );

		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Get address from formatted address
	 *
	 * @param string $formatted_address
	 *
	 * @return bool|GooglePlace
	 */
	public static function get_address_from_formatted_address( $formatted_address ) {
		global $wpdb;
		$self  = new static;
		$table = $wpdb->prefix . $self->table;

		$sql  = $wpdb->prepare( "SELECT * FROM {$table} WHERE formatted_address = %s", $formatted_address );
		$item = $wpdb->get_row( $sql, ARRAY_A );

		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Add place data
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public static function add_place_data( array $data ) {
		$data = self::format_place_data( $data );

		if ( empty( $data['place_id'] ) ) {
			return 0;
		}

		$place_id = ( new static )->create( $data );

		return $place_id;
	}

	/**
	 * Add place data if not exist
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public static function add_place_data_if_not_exist( array $data ) {
		if ( empty( $data['place_id'] ) ) {
			return 0;
		}

		$place = static::get_place_from_place_id( $data['place_id'] );
		if ( $place instanceof self ) {
			return $place->get_id();
		}

		return static::add_place_data( $data );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private static function format_place_data( array $data ) {
		if ( empty( $data['latitude'] ) ) {
			if ( isset( $data['geometry']['location']['lat'] ) && is_numeric( $data['geometry']['location']['lat'] ) ) {
				$data['latitude'] = floatval( $data['geometry']['location']['lat'] );
			} else {
				$data['latitude'] = 0;
			}
		}
		if ( empty( $data['longitude'] ) ) {
			if ( isset( $data['geometry']['location']['lng'] ) && is_numeric( $data['geometry']['location']['lng'] ) ) {
				$data['longitude'] = floatval( $data['geometry']['location']['lng'] );
			} else {
				$data['longitude'] = 0;
			}
		}

		return $data;
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		return [];
	}

	/**
	 * Create database table
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `place_id` text DEFAULT NULL,
                `latitude` float(50) DEFAULT NULL,
                `longitude` float(50) DEFAULT NULL,
                `address_components` text DEFAULT NULL,
                `formatted_address` text DEFAULT NULL,
                `formatted_phone_number` varchar(20) DEFAULT NULL,
                `geometry` text DEFAULT NULL,
                `icon` text DEFAULT NULL,
                `international_phone_number` varchar(20) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `rating` float(4) DEFAULT NULL,
                `reference` text DEFAULT NULL,
                `reviews` text DEFAULT NULL,
                `types` text DEFAULT NULL,
                `url` text DEFAULT NULL,
                `utc_offset` int(4) DEFAULT NULL,
                `vicinity` text DEFAULT NULL,
                `website` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
