<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Supports\DistanceCalculator;

class TrackableObjectTimeline extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_object_timeline';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'                 => 0,
		'object_id'          => '',
		'timeline_date'      => '',
		'timeline_data'      => [],
		'complete_log_count' => 0,
		'complete'           => 0,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%d', '%d' ];

	/**
	 * Get timeline id
	 *
	 * @return int
	 */
	public function get_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get timeline data
	 * @return array
	 */
	public function get_timeline_data() {
		return $this->get( 'timeline_data' );
	}

	/**
	 * Get complete log count
	 *
	 * @return int
	 */
	public function get_complete_log_count() {
		return intval( $this->get( 'complete_log_count' ) );
	}

	public static function get_object_timeline( array $logs, $object_id, $log_date ) {
		$length                = 0;
		$timeline_id           = 0;
		$current_timeline_logs = [];

		$timeline = self::get_timeline( $object_id, $log_date );
		if ( $timeline instanceof self ) {
			$timeline_id           = $timeline->get_id();
			$current_timeline_logs = $timeline->get_timeline_data();
			$length                = $timeline->get_complete_log_count();
			array_splice( $logs, 0, $length );
		}

		$new_counts = count( $logs );

		$logs              = static::get_duration( $logs );
		$new_timeline_logs = static::calculate_timeline( $logs );

		$current_logs_count = count( $current_timeline_logs );
		$new_logs_count     = count( $new_timeline_logs );

		if ( $current_logs_count > 0 && $new_logs_count > 0 ) {
			$last_log  = $current_timeline_logs[ $current_logs_count - 1 ];
			$first_log = $new_timeline_logs[0];
			// Check if current timeline last log and new timeline first log is street address
			if ( isset( $last_log['start_time'], $first_log['end_time'] ) ) {
				$current_timeline_logs[ $current_logs_count - 1 ] = [
					'start_time' => $last_log['start_time'],
					'end_time'   => $first_log['end_time'],
					'duration'   => ( $first_log['duration'] + $last_log['duration'] ),
				];
				array_splice( $new_timeline_logs, 0, 1 );
			} // Check if current timeline last log and new timeline first log is not street address and same
			else {
				// @TODO perform address same check
			}
		}

		$timeline_data = array_merge( $current_timeline_logs, $new_timeline_logs );

		$data = [
			'id'                 => $timeline_id,
			'object_id'          => $object_id,
			'timeline_date'      => $log_date,
			'complete_log_count' => ( $length + $new_counts ),
			'timeline_data'      => $timeline_data,
			'complete'           => 0,
		];

		if ( $timeline_id ) {
			( new static )->update( $data );
		} else {
			( new static )->create( $data );
		}

		return $timeline_data;
	}

	/**
	 * @param array $logs
	 *
	 * @return array
	 */
	public static function calculate_timeline( array $logs ) {
		$new_logs       = [];
		$street_address = [];
		$total_logs     = count( $logs );
		foreach ( $logs as $index => $log ) {
			$is_street_address = in_array( 'street_address', $log['address_types'] );
			$duration          = isset( $log['duration'] ) ? $log['duration'] : 0;
			if ( $is_street_address || $duration < 60 ) {
				$street_address[] = $log;
			} else {
				$count_street_address = count( $street_address );
				if ( $count_street_address > 0 ) {
					$first_street = $street_address[0];
					$last_street  = $street_address[ $count_street_address - 1 ];
					$new_logs[]   = [
						'start_time' => $first_street['utc_timestamp'],
						'end_time'   => $last_street['utc_timestamp'],
						'duration'   => array_sum( wp_list_pluck( $street_address, 'duration' ) ),
						'distance'   => DistanceCalculator::getDistance(
							$first_street['latitude'],
							$first_street['longitude'],
							$last_street['latitude'],
							$last_street['longitude']
						),
					];
				}
				$new_logs[]     = $log;
				$street_address = [];
			}

			// If this is last log and street address has some content
			if ( $index == ( $total_logs - 1 ) && count( $street_address ) > 0 ) {
				$count_street_address = count( $street_address );
				$first_street         = $street_address[0];
				$last_street          = $street_address[ $count_street_address - 1 ];
				$new_logs[]           = [
					'start_time' => $first_street['utc_timestamp'],
					'end_time'   => $last_street['utc_timestamp'],
					'duration'   => array_sum( wp_list_pluck( $street_address, 'duration' ) ),
					'distance'   => DistanceCalculator::getDistance(
						$first_street['latitude'],
						$first_street['longitude'],
						$last_street['latitude'],
						$last_street['longitude']
					),
				];
				$street_address       = [];
			}
		}

		return $new_logs;
	}

	public static function get_duration( $logs ) {
		$total_logs = count( $logs );
		$new_logs   = [];
		foreach ( $logs as $index => $log ) {
			$new_logs[ $index ] = $log;

			if ( $index < ( $total_logs - 1 ) ) {
				$next_log = $logs[ $index + 1 ];

				$new_logs[ $index ]['duration'] = intval( $next_log['utc_timestamp'] ) - intval( $log['utc_timestamp'] );
			} else {
				$new_logs[ $index ]['duration'] = intval( current_time( 'timestamp' ) - $log['utc_timestamp'] );
			}

			$should_get_address = ! in_array( 'street_address', $log['address_types'] );
			if ( $index == 0 || $index == ( $total_logs - 1 ) ) {
				$should_get_address = true;
			}

			if ( $should_get_address ) {
				$address = GoogleMap::get_address_from_place_id( $log['place_id'] );

				$new_logs[ $index ]['address'] = [
					'name'              => $address['name'],
					'icon'              => $address['icon'],
					'formatted_address' => $address['formatted_address'],
				];
			}
		}

		return $new_logs;
	}

	/**
	 * Get object timeline
	 *
	 * @param string $object_id
	 * @param string $date
	 *
	 * @return bool|self
	 */
	public static function get_timeline( $object_id, $date ) {
		$self = new static;
		global $wpdb;
		$table = $wpdb->prefix . $self->table;

		$sql  = "SELECT * FROM {$table} WHERE 1 = 1";
		$sql  .= $wpdb->prepare( " AND object_id = %s", $object_id );
		$sql  .= $wpdb->prepare( " AND timeline_date = %s", $date );
		$item = $wpdb->get_row( $sql, ARRAY_A );

		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Create timeline
	 *
	 * @param array $data
	 */
	public static function create_timeline( array $data ) {
		if ( empty( $data['timeline_date'] ) ) {
			$data['timeline_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
		}

		( new static )->create( $data );
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
                `timeline_date` date DEFAULT NULL,
                `timeline_data` longtext DEFAULT NULL,
                `complete_log_count` int(11) DEFAULT null,
                `complete` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
