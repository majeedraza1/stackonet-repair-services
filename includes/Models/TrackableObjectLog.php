<?php

namespace Stackonet\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Supports\DistanceCalculator;
use Stackonet\Supports\Logger;

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
	 * @param string $date
	 *
	 * @return DateTime[]
	 * @throws Exception
	 */
	public static function get_periods( $date ) {
		$date1 = new DateTime( $date );
		$date1->modify( 'midnight' );

		$date2 = new DateTime( $date );
		$date2->modify( 'tomorrow -1 second' );

		$interval = new DateInterval( 'PT1H' );
		/** @var DateTime[] $period */
		$period = new DatePeriod( $date1, $interval, $date2 );

		return $period;
	}

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

		// $logs = array_merge( $temp[7], $temp[8], $temp[9], $temp[10], $temp[11], $temp[12] ); // Temple rode
//		$temp = array_chunk( $logs, 10 );
//		$logs = array_merge( $temp[2], $temp[3], $temp[4], $temp[5] ); // Only street view

		return $logs;
	}

	/**
	 * Get log data with distance and duration
	 *
	 * @return array
	 */
	public function get_log_data_with_distance_and_duration() {
		$_logs = $this->get_log_data();
		$logs  = [];
		foreach ( $_logs as $index => $log ) {
			$logs[ $index ] = $log;
			if ( 0 !== $index ) {
				$pre = $_logs[ $index - 1 ];

				$logs[ $index ]['distance'] = DistanceCalculator::getDistance(
					$pre['latitude'],
					$pre['longitude'],
					$log['latitude'],
					$log['longitude']
				);
			}

			if ( $index < count( $_logs ) - 1 ) {
				$next = $_logs[ $index + 1 ];

				$logs[ $index ]['duration'] = intval( $next['utc_timestamp'] - $log['utc_timestamp'] );
			}

			if ( $index == count( $_logs ) - 1 ) {
				$logs[ $index ]['duration'] = intval( current_time( 'timestamp' ) - $log['utc_timestamp'] );
			}

			if ( isset( $logs[ $index ]['duration'] ) && $logs[ $index ]['duration'] >= ( 4 * MINUTE_IN_SECONDS ) ) {
				$location = GoogleMap::get_addresses_from_lat_lng(
					$log['latitude'],
					$log['longitude']
				);

				$logs[ $index ]['location'] = wp_list_pluck( $location, 'formatted_address' );
			}
		}

		return $logs;
	}

	/**
	 * Get log data by time range
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_log_data_by_time_range() {
		$date  = $this->get( 'log_date' );
		$_logs = $this->get_log_data();

		$period = self::get_periods( $date );

		$_times = [];
		foreach ( $period as $day ) {
			$_times[] = $day->getTimestamp();
		}

		$logs = [];
		foreach ( $period as $index => $_dateTime ) {

			$logs[ $index ] = [
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

			if ( is_array( $_logs ) && count( $_logs ) ) {
				foreach ( $_logs as $log ) {
					if ( $log['utc_timestamp'] >= $_current && $log['utc_timestamp'] < $_next ) {
						$logs[ $index ]['logs'][] = [
							'latitude'  => $log['latitude'],
							'longitude' => $log['longitude']
						];
					}
				}
			}
		}

		return $logs;
	}

	/**
	 * Get log data by time range
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_snapped_points_by_time_range() {
		$transient_name       = sprintf( '_snapped_points_%s_%s', $this->get( 'object_id' ), $this->get( 'log_date' ) );
		$transient_expiration = MINUTE_IN_SECONDS;
		$points               = get_transient( $transient_name );

		if ( false === $points ) {
			$points  = [];
			$periods = $this->get_log_data_by_time_range();
			foreach ( $periods as $period ) {
				$total_logs = isset( $period['logs'] ) && is_array( $period['logs'] ) ? count( $period['logs'] ) : 0;
				if ( $total_logs ) {
					if ( $total_logs > 2 ) {
						if ( ! empty( $last_log ) ) {
							array_unshift( $period['logs'], $last_log );
						}
						$_snapped_points = GoogleMap::get_snapped_points( $period['logs'] );
						$snapped_points  = [];
						foreach ( $_snapped_points as $snapped_point ) {
							if ( isset( $snapped_point['location'] ) ) {
								$snapped_points[] = $snapped_point['location'];
							}
						}

						$period['logs'] = $snapped_points;
					}
					$last_log = $period['logs'][ $total_logs - 1 ];
				}
				$points[] = $period;
			}
			set_transient( $transient_name, $points, $transient_expiration );
		}

		return $points;
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

		if ( $log_data_count < 1 ) {
			return;
		}

		$last_item = $log_data[ $log_data_count - 1 ];

		$diff = array_diff( $log, $last_item );
		if ( isset( $diff['utc_timestamp'] ) ) {
			unset( $diff['utc_timestamp'] );
		}

		if ( count( $diff ) < 1 ) {
			return;
		}


		$last_address    = self::get_last_log_address( $last_item );
		$current_address = self::get_current_log_address( $log );
		$data_changed    = ( $last_address['place_id'] !== $current_address['place_id'] );

		if ( 0 === strcmp( strtolower( $last_address['formatted_address'] ), strtolower( $current_address['formatted_address'] ) ) ) {
			$data_changed = false;
		}

		// Save data, if data changed
		if ( $data_changed ) {
			$log_data[] = $log;
			$this->set( 'log_data', $log_data );
			$this->update( $this->data );
		}
	}

	public static function get_last_log_address( $log ) {
		$transient_name       = 'last_log_address_' . md5( wp_json_encode( $log ) );
		$transient_expiration = MINUTE_IN_SECONDS * 10;
		$address              = get_transient( $transient_name );
		if ( false !== $address ) {
			return $address;
		}

		$addressObject = GoogleMap::get_address_from_lat_lng( $log['latitude'], $log['longitude'] );
		$address       = [
			'place_id'          => $addressObject['place_id'],
			'formatted_address' => $addressObject['formatted_address'],
			'types'             => $addressObject['types'],
		];
		set_transient( $transient_name, $address, $transient_expiration );

		return $address;
	}

	public static function get_current_log_address( $log ) {
		$addressObject = GoogleMap::get_address_from_lat_lng( $log['latitude'], $log['longitude'] );
		$address       = [
			'place_id'          => $addressObject['place_id'],
			'formatted_address' => $addressObject['formatted_address'],
			'types'             => $addressObject['types'],
		];

		return $address;
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

		$_objects        = TrackableObject::getActiveObjects();
		$objects_ids     = wp_list_pluck( $_objects, 'object_id' );
		$current_records = self::get_current_records( $object_ids, $current_time );

		foreach ( $objects as $object_id => $log ) {
			if ( ! in_array( $object_id, $objects_ids ) ) {
				continue;
			}
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
	 * Find object log
	 *
	 * @param string $object_id
	 * @param string $date
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

	/**
	 * Find min and max log date from log database
	 *
	 * @param string $object_id
	 *
	 * @return array
	 */
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
