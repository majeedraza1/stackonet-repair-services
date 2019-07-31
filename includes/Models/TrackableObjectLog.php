<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class TrackableObjectLog extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_trackable_object_log';

	/**
	 * @var string
	 */
	protected $cache_group = 'stackonet_trackable_object_log';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'        => 0,
		'object_id' => null,
		'log_date'  => null,
		'log_data'  => null,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s' ];

	/**
	 * Add log data
	 *
	 * @param array $log
	 */
	public function add_log_data( array $log ) {
		$log_data       = $this->get( 'log_data' );
		$log_data       = is_array( $log_data ) ? $log_data : [];
		$log_data_count = count( $log_data );
		$data_changed   = false;
		if ( $log_data_count ) {
			$last_item = end( $log_data );
			$diff      = array_diff( $log, $last_item );
			if ( isset( $diff['utc_timestamp'] ) ) {
				unset( $diff['utc_timestamp'] );
			}
			if ( count( $diff ) ) {
				$log_data[]   = $log;
				$data_changed = true;
			}
		}
		// Save data, if data changed
		if ( $data_changed ) {
			$this->set( 'log_data', $log_data );
			$this->update( $this->data );
		}
	}

	/**
	 * @param array $objects
	 *
	 * @return bool
	 */
	public static function log_objects( $objects = [] ) {
		$self = new static;
		global $wpdb;
		$table        = $wpdb->prefix . $self->table;
		$object_ids   = array_keys( $objects );
		$object_ids   = array_map( 'esc_sql', $object_ids );
		$current_time = current_time( 'timestamp' );
		$date         = date( 'Y-m-d', $current_time );

		$current_records = self::get_current_records( $object_ids, $current_time );

		foreach ( $objects as $object_id => $log ) {
			if ( isset( $current_records[ $object_id ] ) ) {
				/** @var self $_self */
				$_self = $current_records[ $object_id ];
				$_self->add_log_data( $log );
			} else {
				$object = [
					'id'        => 0,
					'object_id' => $object_id,
					'log_date'  => $date,
					'log_data'  => [ $log ],
				];
				$self->create( $object );
			}
		}

		return true;
	}

	/**
	 * @param array $object_ids
	 * @param int $current_timestamp
	 *
	 * @return array|self
	 */
	public static function get_current_records( array $object_ids, $current_timestamp = null ) {
		$self = new static();
		global $wpdb;
		$table = $wpdb->prefix . $self->table;
		if ( empty( $current_timestamp ) ) {
			$current_timestamp = current_time( 'timestamp' );
		}
		$date = date( 'Y-m-d', $current_timestamp );

		$sql     = "SELECT * FROM {$table} WHERE 1 = 1";
		$sql     .= " AND object_id IN (\"" . implode( '", "', $object_ids ) . "\")";
		$sql     .= $wpdb->prepare( " AND log_date = %s", $date );
		$sql     .= " ORDER BY id DESC";
		$results = $wpdb->get_results( $sql, ARRAY_A );

		$current_records = [];
		if ( is_array( $results ) && count( $results ) ) {
			foreach ( $results as $result ) {
				$current_records[ $result['object_id'] ] = new self( [
					'id'        => intval( $result['id'] ),
					'object_id' => $result['object_id'],
					'log_data'  => $self->unserialize( $result['log_data'] ),
					'log_date'  => $result['log_date'],
				] );
			}
		}

		return $current_records;
	}

	/**
	 * Find multiple records from database
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$items  = parent::find( $args );
		$_items = [];
		foreach ( $items as $item ) {
			$_items[] = new self( $item );
		}

		return $_items;
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
                `log_date` date DEFAULT NULL,
                `log_data` longtext DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
