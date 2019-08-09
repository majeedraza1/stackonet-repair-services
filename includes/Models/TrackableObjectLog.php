<?php

namespace Stackonet\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use Stackonet\Abstracts\DatabaseModel;
use Stackonet\DateTime\WpDateTimeZone;

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
	 * Find color code for polyline
	 *
	 * @param int $index
	 *
	 * @return string
	 */
	public function color( $index = 0 ) {
		if ( $index > 10 ) {
			$index = substr( $index, - 1 );
		}
		$colors = [
			'#F44336',
			'#9C27B0',
			'#E91E63',
			'#673AB7',
			'#1E88E5',
			'#3F51B5',
			'#0288D1',
			'#00796B',
			'#43A047',
			'#827717',
			'#E65100',
			'#F4511E'
		];

		return isset( $colors[ $index ] ) ? $colors[ $index ] : null;
	}

	/**
	 * Get log data
	 *
	 * @return array
	 */
	public function get_log_data() {
		$_logs = $this->get( 'log_data' );
		$logs  = [];
		foreach ( $_logs as $log ) {
			if ( empty( $log['latitude'] ) || empty( $log['longitude'] ) ) {
				continue;
			}

			$logs[] = $log;
		}

		return $logs;
	}

	public function get_log_data_by_time_range() {
		$date  = $this->get( 'log_date' );
		$_logs = $this->get_log_data();

		$timezone = WpDateTimeZone::getWpTimezone();

		$date1 = new \DateTime( $date );
		$date1->modify( 'midnight' );

		$date2 = new \DateTime( $date );
		$date2->modify( 'tomorrow -1 second' );

		$interval = new DateInterval( 'PT1H' );
		/** @var DateTime[] $period */
		$period = new DatePeriod( $date1, $interval, $date2 );

		$_times = [];
		foreach ( $period as $day ) {
			$_times[] = $day->getTimestamp();
		}

		$logs = [];
		foreach ( $period as $index => $_dateTime ) {

			$logs[ $index ] = [
				'dateTime'  => $_dateTime->format( DateTime::ISO8601 ),
				'colorCode' => $this->color( $index ),
			];

			$_current = $_dateTime->getTimestamp();
			$_dateTime->modify( '+ 1 hour' );
			$_next = $_dateTime->getTimestamp();
			$_dateTime->modify( '- 1 hour' );

			$logs[ $index ]['title'] = sprintf(
				"From %s to %s",
				date( 'ha', $_current ),
				date( 'ha', $_next )
			);

			foreach ( $_logs as $log ) {
				if ( $log['utc_timestamp'] >= $_current && $log['utc_timestamp'] < $_next ) {
					$logs[ $index ]['logs'][] = [ 'latitude' => $log['latitude'], 'longitude' => $log['longitude'] ];
				}
			}
		}

		return $logs;
	}

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
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primaryKey;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( ! empty( $args['log_date'] ) ) {
			$query .= $wpdb->prepare( " AND log_date = %s", $args['log_date'] );
		}

		if ( ! empty( $args['object_id'] ) ) {
			$query .= $wpdb->prepare( " AND object_id = %s", $args['object_id'] );
		}

		if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
			$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
		}

		$query .= " ORDER BY {$orderby} {$order}";
		$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );
		$items = $wpdb->get_results( $query, ARRAY_A );

		$_items = [];

		foreach ( $items as $item ) {
			$_items[] = new self( $item );
		}

		return $_items;
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
	 * @param string|null $date
	 *
	 * @return self
	 */
	public function find_object_log( $object_id, $date ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";
		$query .= $wpdb->prepare( " AND object_id = %s", $object_id );
		$query .= $wpdb->prepare( " AND log_date = %s", $date );
		$query .= " ORDER BY id DESC";
		$item  = $wpdb->get_row( $query, ARRAY_A );
		if ( $item ) {
			return new self( $item );
		}

		return null;
	}


	public static function find_min_max_log_date( $object_id ) {
		global $wpdb;
		$self  = new self();
		$table = $wpdb->prefix . $self->table;

		$first = $wpdb->prepare(
			"(SELECT `log_date` FROM {$table} WHERE `object_id` = %s ORDER BY log_date ASC LIMIT 1) as startDate",
			$object_id
		);

		$last = $wpdb->prepare(
			"(SELECT `log_date` FROM {$table} WHERE `object_id` = %s ORDER BY log_date DESC LIMIT 1) as endDate",
			$object_id
		);

		$sql   = "SELECT {$first}, {$last}";
		$items = $wpdb->get_row( $sql, ARRAY_A );

		return $items;
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
