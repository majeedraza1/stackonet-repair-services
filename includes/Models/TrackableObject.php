<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Supports\Validate;

class TrackableObject extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_trackable_object';

	/**
	 * @var string
	 */
	protected $cache_group = 'stackonet_trackable_object';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'          => 0,
		'object_id'   => null,
		'object_name' => null,
		'object_type' => null,
		'object_icon' => null,
		'active'      => 0,
		'created_by'  => 0,
		'created_at'  => null,
		'updated_at'  => null,
		'deleted_at'  => null,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ];


	/**
	 * Log data
	 *
	 * @var array
	 */
	protected $log_data = [];

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$data         = parent::to_array();
		$data['icon'] = $this->get_object_icon();

		return $data;
	}

	/**
	 * @param $date
	 *
	 * @return array
	 */
	public function to_rest( $date ) {
		$response = [
			'id'          => intval( $this->get( 'id' ) ),
			'object_id'   => $this->get( 'object_id' ),
			'object_name' => $this->get( 'object_name' ),
			'object_type' => $this->get( 'object_type' ),
			'icon'        => $this->get_object_icon(),
			'last_log'    => [],
			'logs'        => [],
		];

		$objectLog = ( new TrackableObjectLog() )->find_object_log( $this->get( 'object_id' ), $date );
		if ( $objectLog instanceof TrackableObjectLog ) {
			$logs     = $objectLog->get_log_data();
			$last_log = end( $logs );

			$response['logs']     = $logs;
			$response['last_log'] = is_array( $last_log ) ? $last_log : [];
			$response['online']   = $objectLog->is_online();
		}

		return $response;
	}

	/**
	 * Get object icon
	 *
	 * @return string|null
	 */
	public function get_object_icon() {
		$icon_id = intval( $this->get( 'object_icon' ) );
		if ( $icon_id ) {
			$src = wp_get_attachment_image_src( $icon_id );

			return isset( $src[0] ) ? $src[0] : null;
		}

		return null;
	}

	public function get_log_data( $date = null ) {
		if ( empty( $this->log_data[ $date ] ) ) {
			$item = ( new TrackableObjectLog() )->find_object_log( $this->get( 'object_id' ), $date );
			if ( $item instanceof TrackableObjectLog ) {
				return $this->log_data[ $date ] = $item->get_log_data();
			}
		}

		return ! empty( $this->log_data[ $date ] ) ? $this->log_data[ $date ] : null;
	}

	/**
	 * Find multiple records from database
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$results = parent::find( $args );
		$items   = [];
		foreach ( $results as $result ) {
			$items[] = new self( $result );
		}

		return $items;
	}

	/**
	 * Get active objects
	 *
	 * @return array
	 */
	public static function getActiveObjects() {
		global $wpdb;
		$self  = new static();
		$table = $wpdb->prefix . $self->table;

		$query   = "SELECT * FROM {$table} WHERE deleted_at IS NULL";
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}

	/**
	 * Find record by id
	 *
	 * @param int $id
	 *
	 * @return array|self
	 */
	public function find_by_id( $id ) {
		$item = parent::find_by_id( $id );

		return new self( $item );
	}

	/**
	 * @param string $object_id
	 *
	 * @return bool|self
	 */
	public function find_by_object_id( $object_id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$sql  = "SELECT * FROM {$table} WHERE `object_id` = %s";
		$item = $wpdb->get_row( $wpdb->prepare( $sql, $object_id ), ARRAY_A );
		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Find min max log date
	 *
	 * @return array|object|null
	 */
	public function find_min_max_log_date() {
		return TrackableObjectLog::find_min_max_log_date( $this->get( 'object_id' ) );
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
                `object_id` VARCHAR(20) DEFAULT NULL,
                `object_name` VARCHAR(100) DEFAULT NULL,
                `object_type` VARCHAR(50) DEFAULT NULL,
                `object_icon` VARCHAR(50) DEFAULT NULL,
                `active` tinyint(1) DEFAULT 0,
                `created_by` bigint(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}

	/**
	 * Add initial users
	 */
	public static function add_initial_users() {
		$object = new TrackableObject();
		$item   = $object->find_by_object_id( 'van1' );
		if ( ! $item instanceof TrackableObject ) {
			$object->create( [
				'object_id'   => 'van1',
				'object_name' => 'Van 1',
				'object_type' => 'van',
				'object_icon' => '6274',
				'active'      => 1,
			] );
		}
		$item = $object->find_by_object_id( 'van2' );
		if ( ! $item instanceof TrackableObject ) {
			$object->create( [
				'object_id'   => 'van2',
				'object_name' => 'Van 2',
				'object_type' => 'van',
				'object_icon' => '6273',
				'active'      => 1,
			] );
		}
	}
}
