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

	/**
	 * Prepare timeline logs for REST
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	public static function format_timeline_for_rest( array $logs ) {
		$new_logs = [];

		$logs = self::update_last_log( $logs );

		foreach ( $logs as $log ) {

			if ( isset( $log['street_address'] ) && $log['street_address'] ) {

				$new_logs[] = array_merge( $log, [
					'type'                 => 'movement',
					'icon'                 => 'https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_moving_black_24dp.png',
					'activityType'         => 'Moving',
					'activityDistanceText' => DistanceCalculator::meter_to_human( $log['distance'] ),
					'activityDurationText' => human_time_diff( $log['start_timestamp'], $log['end_timestamp'] ),
				] );

			}
			if ( isset( $log['street_address'] ) && ! $log['street_address'] && ! empty( $log['address'] ) ) {

				$new_logs[] = array_merge( $log, [
					'type'                 => 'place',
					'dateTime'             => date( \DateTime::ISO8601, $log['utc_timestamp'] ),
					'activityDurationText' => date( 'h:i A', $log['utc_timestamp'] ),
					'addresses'            => [ $log['address'] ],
				] );
			}
		}

		return $new_logs;
	}

	/**
	 * Format timeline from logs
	 *
	 * @param array $logs
	 * @param string $object_id
	 * @param string $log_date
	 *
	 * @return array
	 */
	public static function format_timeline_from_logs( array $logs, $object_id, $log_date ) {
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

		// If there is no new log, then return current timeline logs
		$new_counts = count( $logs );
		if ( $new_counts < 1 ) {
			return $current_timeline_logs;
		}

		$logs              = static::add_duration_and_distance( $logs );
		$logs              = static::format_log( $logs );
		$new_timeline_logs = static::add_address_data( $logs );

		$timeline_data = array_merge( $current_timeline_logs, $new_timeline_logs );

		// If first log is street address, update it
		if ( count( $current_timeline_logs ) < 1 && count( $new_timeline_logs ) > 0 ) {
			$timeline_data = self::update_first_log( $timeline_data );
		}

		$timeline_data = self::merge_consecutive_places( $timeline_data );
		$timeline_data = self::merge_consecutive_street_addresses( $timeline_data );

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
	 * Calculate timeline data
	 * =======================================================================
	 * 01. Loop through all logs
	 * 02. If the log is a street address or duration is less than 60 seconds
	 *     --> i. Add the log to temporary $street_address variable as an item
	 *
	 * 03. If the log is not a street address
	 *     --> i.   Check if temporary $street_address contains any address,
	 *                  if it contains any address then save it to $new_logs
	 *    --> ii.   Also save current address
	 *    --> iii.  On last log item, If street address has some content, save it also
	 * =======================================================================
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	public static function format_log( array $logs ) {
		$new_logs       = [];
		$street_address = [];
		$total_logs     = count( $logs );

		foreach ( $logs as $index => $log ) {
			$is_street_address = $log['street_address'];
			$duration          = isset( $log['duration'] ) ? $log['duration'] : 0;

			if ( $is_street_address || $duration < 15 ) {
				$street_address[] = $log;
			} else {
				if ( count( $street_address ) > 0 ) {
					$new_logs[]     = self::format_street_address( $street_address );
					$street_address = [];
				}
				$new_logs[] = $log;
			}

			// If this is last log and street address has some content
			if ( $index == ( $total_logs - 1 ) && count( $street_address ) > 0 ) {
				$new_logs[]     = self::format_street_address( $street_address );
				$street_address = [];
			}
		}

		return $new_logs;
	}

	/**
	 * Get duration from one location to another location
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	public static function add_duration_and_distance( array $logs ) {
		$total_logs = count( $logs );
		$new_logs   = [];
		foreach ( $logs as $index => $log ) {
			$new_logs[ $index ] = $log;

			if ( $index < ( $total_logs - 1 ) ) {
				$next_log = $logs[ $index + 1 ];

				$new_logs[ $index ]['duration'] = intval( $next_log['utc_timestamp'] ) - intval( $log['utc_timestamp'] );
				$new_logs[ $index ]['distance'] = DistanceCalculator::getDistance(
					$log['latitude'],
					$log['longitude'],
					$next_log['latitude'],
					$next_log['longitude']
				);
			} else {
				$new_logs[ $index ]['duration'] = intval( current_time( 'timestamp' ) - $log['utc_timestamp'] );
				$new_logs[ $index ]['distance'] = 0;
			}

			$new_logs[ $index ]['street_address'] = self::is_street_address( $log['address_types'] );
		}

		return $new_logs;
	}

	/**
	 * Get address data to logs
	 * Only add address for first log, last log and log other than street address
	 *
	 * @param array $logs
	 *
	 * @return array
	 *
	 * @todo Getting address from Google Map API is slow and server can stop working for long data.
	 * @todo Update functionality to get address in background
	 */
	public static function add_address_data( array $logs ) {
		$total_logs = count( $logs );
		$new_logs   = [];
		foreach ( $logs as $index => $log ) {
			$new_logs[ $index ] = $log;

			if ( empty( $log['place_id'] ) ) {
				continue;
			}

			if ( false == $log['street_address'] || $index == 0 || $index == ( $total_logs - 1 ) ) {
				$address = GoogleMap::get_address_from_place_id( $log['place_id'] );

				unset( $new_logs[ $index ]['place_id'] );

				$new_logs[ $index ]['address'] = [
					'place_id'          => $log['place_id'],
					'name'              => $address['name'],
					'icon'              => $address['icon'],
					'formatted_address' => $address['formatted_address'],
				];
			}
		}

		return $new_logs;
	}

	/**
	 * Format street address for saving on timeline database
	 *
	 * @param array $street_address
	 *
	 * @return array
	 */
	private static function format_street_address( array $street_address ) {
		$end_log = end( $street_address );

		return [
			'street_address'  => true,
			'start_timestamp' => $street_address[0]['utc_timestamp'],
			'end_timestamp'   => $end_log['utc_timestamp'],
			'duration'        => array_sum( wp_list_pluck( $street_address, 'duration' ) ),
			'distance'        => array_sum( wp_list_pluck( $street_address, 'distance' ) ),
			'steps'           => $street_address
		];
	}

	/**
	 * Check provided address is a street address
	 *
	 * @param array $address_types
	 *
	 * @return bool
	 */
	public static function is_street_address( $address_types ) {
		if ( in_array( 'street_address', $address_types ) ) {
			return true;
		}
		if ( in_array( 'route', $address_types ) ) {
			return true;
		}

		return false;
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
	 * Merge two consecutive same address
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private static function merge_consecutive_places( array $logs ) {
		$new_logs   = [];
		$last_place = [];

		foreach ( $logs as $index => $log ) {
			if ( ! $log['street_address'] ) {
				if ( ! empty( $last_place ) ) {
					$last_place_name    = preg_replace( "/[0-9 ]/", '', $last_place['address']['name'] );
					$current_place_name = preg_replace( "/[0-9 ]/", '', $log['address']['name'] );
					if ( strtolower( $last_place_name ) != strtolower( $current_place_name ) ) {
						$new_logs[] = $log;
					}
				} else {
					$new_logs[] = $log;
				}
				$last_place = $log;
			} else {
				$new_logs[] = $log;
			}
		}

		return $new_logs;
	}

	/**
	 * Merge two consecutive street address
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private static function merge_consecutive_street_addresses( array $logs ) {
		$_logs           = [];
		$last_street_log = [];
		$total_logs      = count( $logs );
		foreach ( $logs as $index => $log ) {
			if ( $log['street_address'] ) {
				if ( ! empty( $last_street_log ) ) {
					$last_street_log['steps']         = array_merge( $last_street_log['steps'], $log['steps'] );
					$last_street_log['duration']      = ( $last_street_log['duration'] + $log['duration'] );
					$last_street_log['distance']      = ( $last_street_log['distance'] + $log['distance'] );
					$last_street_log['end_timestamp'] = $log['end_timestamp'];
				} else {
					$last_street_log = $log;
				}

				if ( $index == ( $total_logs - 1 ) && ! empty( $last_street_log ) ) {
					$_logs[]         = $last_street_log;
					$last_street_log = [];
				}
			} else {
				if ( ! empty( $last_street_log ) ) {
					$_logs[]         = $last_street_log;
					$last_street_log = [];
				}

				$_logs[] = $log;
			}
		}

		return $_logs;
	}

	/**
	 * Update first log for response
	 * We need address for first log even it is street address
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private static function update_first_log( array $logs ) {
		$first_log = $logs[0];
		if ( $first_log['street_address'] ) {
			$_steps     = $first_log['steps'];
			$_first_log = $_steps[0];

			$address = GoogleMap::get_address_from_place_id(
				$_first_log['place_id']
			);

			$_first_log['address'] = [
				'place_id'          => $_first_log['place_id'],
				'name'              => $address['name'],
				'icon'              => $address['icon'],
				'formatted_address' => $address['formatted_address'],
			];

			$_first_log['street_address'] = false;

			unset( $_first_log['place_id'] );

			if ( count( $_steps ) == 1 ) {
				$logs[0] = $_first_log;
			} else {
				array_splice( $_steps, 0, 1 );
				$street_address = self::format_street_address( $_steps );
				$logs[0]        = $street_address;
				array_unshift( $logs, $_first_log );
			}
		}

		return $logs;
	}

	/**
	 * Update last log for response
	 * We need address for last log even it is street address
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private static function update_last_log( array $logs ) {
		$last_log = $logs[ count( $logs ) - 1 ];
		if ( $last_log['street_address'] ) {
			$_steps    = $last_log['steps'];
			$_last_log = $_steps[ count( $_steps ) - 1 ];

			$address = GoogleMap::get_address_from_place_id(
				$_last_log['place_id']
			);

			$_last_log['address'] = [
				'place_id'          => $_last_log['place_id'],
				'name'              => $address['name'],
				'icon'              => $address['icon'],
				'formatted_address' => $address['formatted_address'],
			];

			$_last_log['street_address'] = false;

			unset( $_last_log['place_id'] );

			if ( count( $_steps ) == 1 ) {
				$logs[ count( $logs ) - 1 ] = $_last_log;
			} else {
				$logs[ count( $logs ) - 1 ] = self::format_street_address( $_steps );
				$logs[]                     = $_last_log;
			}
		}

		return $logs;
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
