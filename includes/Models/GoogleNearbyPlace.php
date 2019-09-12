<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Supports\Logger;

defined( 'ABSPATH' ) || exit;

class GoogleNearbyPlace extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_google_nearby_place';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'         => '',
		'latitude'   => '',
		'longitude'  => '',
		'places'     => '',
		'created_at' => '',
		'updated_at' => '',
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%s' ];

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$items = [];
		foreach ( $this->get_places() as $place ) {
			$items[] = $place->to_timeline();
		}

		return $items;
	}

	/**
	 * Get place id
	 *
	 * @return int
	 */
	public function get_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * get nearby places
	 *
	 * @return GooglePlace[]
	 */
	public function get_places() {
		$_places = $this->get( 'places' );
		$_places = is_array( $_places ) ? $_places : [];
		$places = [];
		foreach ( $_places as $place ) {
			$places[] = new GooglePlace( GooglePlace::format_place_data( $place ) );
		}

		return $places;
	}

	/**
	 * @param int|float $latitude
	 * @param int|float $longitude
	 *
	 * @return self|false
	 */
	public static function get_places_from_lat_lng( $latitude, $longitude ) {
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
	 * Add place data if not exists
	 *
	 * @param float $latitude
	 * @param float $longitude
	 * @param array $places
	 */
	public static function add_place_data_if_not_exist( $latitude, $longitude, $places ) {
		$place = static::get_places_from_lat_lng( $latitude, $longitude );
		if ( ! $place instanceof static ) {
			( new static() )->create( [
				'latitude'  => $latitude,
				'longitude' => $longitude,
				'places'    => $places,
			] );
		}
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
                `latitude` float(50) DEFAULT NULL,
                `longitude` float(50) DEFAULT NULL,
                `places` longtext DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
