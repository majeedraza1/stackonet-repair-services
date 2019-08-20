<?php

namespace Stackonet\Integrations;

use Stackonet\Models\TrackableObjectLog;
use Stackonet\Supports\Logger;

class FirebaseDatabase {

	/**
	 * Firebase project id
	 *
	 * @var string
	 */
	protected $project_id = 'stackonet-services';

	/**
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'init', [ self::$instance, 'schedule_cron_event' ] );
			add_action( 'sync_firebase_employees', [ self::$instance, 'sync_employees' ] );
		}

		return self::$instance;
	}

	public function schedule_cron_event() {
		if ( ! wp_next_scheduled( 'sync_firebase_employees' ) ) {
			wp_schedule_event( time(), 'every_minute', 'sync_firebase_employees' );
		}
	}

	/**
	 * Sync employees
	 */
	public static function sync_employees() {
		$employees = ( new static )->getEmployees();
		TrackableObjectLog::log_objects( $employees );
	}

	/**
	 * Get firebase database url
	 *
	 * @return string
	 */
	public function get_database_url() {
		return sprintf( "https://%s.firebaseio.com", $this->get_project_id() );
	}

	/**
	 * Get project id
	 *
	 * @return string
	 */
	public function get_project_id() {
		return $this->project_id;
	}

	/**
	 * Get employ data from firebase database
	 *
	 * @return array
	 */
	public function getEmployees() {
		$url      = $this->get_database_url() . "/Employees.json";
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body    = wp_remote_retrieve_body( $response );
		$objects = json_decode( $body, true );

		$current_time = current_time( 'timestamp' );

		$items = [];
		foreach ( $objects as $object ) {
			$object_id = isset( $object['Employee_ID'] ) ? $object['Employee_ID'] : null;
			if ( empty( $object_id ) ) {
				continue;
			}
			$items[ $object_id ] = [
				'latitude'      => isset( $object['latitude'] ) ? $object['latitude'] : null,
				'longitude'     => isset( $object['longitude'] ) ? $object['longitude'] : null,
				'online'        => isset( $object['online'] ) && $object['online'] == 'true',
				'utc_timestamp' => $current_time,
			];
		}

		return $items;
	}
}
