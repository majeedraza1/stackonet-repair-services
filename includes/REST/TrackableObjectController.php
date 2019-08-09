<?php

namespace Stackonet\REST;

use Stackonet\Integrations\FirebaseDatabase;
use Stackonet\Models\Settings;
use Stackonet\Models\TrackableObject;
use Stackonet\Models\TrackableObjectLog;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TrackableObjectController extends ApiController {
	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/trackable-objects', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'args'     => $this->get_collection_params(),
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
			],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/logs', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_logs' ] ],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/logs/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_log' ] ],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/log', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_locations' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'log_location' ] ]
		] );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		FirebaseDatabase::sync_employees();

		$timestamp = current_time( 'timestamp', true );
		$date      = date( 'Y-m-d', $timestamp );
		$items     = ( new TrackableObject() )->find();

		return $this->respondOK( [
			'utc_timestamp' => $timestamp,
			'idle_time'     => ( 10 * 60 ), // Ten minutes in seconds
			'items'         => $this->prepare_items_for_response( $items, $timestamp ),
			'counts'        => [],
			'pagination'    => []
		] );
	}

	/**
	 * Create one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		$trackableObject = new TrackableObject();

		$object_id   = $request->get_param( 'object_id' );
		$object_icon = $request->get_param( 'object_icon' );

		if ( empty( $object_id ) || empty( $object_icon ) ) {
			return $this->respondUnprocessableEntity( 'Object ID and object icon id is required.' );
		}

		if ( $trackableObject->find_by_object_id( $object_id ) instanceof TrackableObject ) {
			return $this->respondUnprocessableEntity( 'Object ID is already register' );
		}

		$object_id = $trackableObject->create( $request->get_params() );
		$object    = $trackableObject->find_by_id( $object_id );

		return $this->respondCreated( $object );
	}

	/**
	 * Update one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function update_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		$trackableObject = new TrackableObject();
		$object          = $trackableObject->find_by_id( $id );

		if ( ! $object instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$object_id = $request->get_param( 'object_id' );

		if ( ! empty( $object_id ) && $object->get( 'object_id' ) != $object_id ) {
			return $this->respondUnprocessableEntity( 'Object Id cannot be changed.' );
		}

		$data       = $request->get_params();
		$data['id'] = $id;

		$trackableObject->update( $data );

		return $this->respondOK();
	}

	/**
	 * Delete one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		$trackableObject = new TrackableObject();
		$object          = $trackableObject->find_by_id( $id );

		if ( ! $object instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		if ( $trackableObject->delete( $id ) ) {
			return $this->respondOK( [ 'id' => $id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * @param TrackableObject[] $items
	 * @param int $timestamp
	 *
	 * @return array
	 */
	public function prepare_items_for_response( $items, $timestamp = null ) {
		if ( empty( $timestamp ) ) {
			$timestamp = current_time( 'timestamp', true );
		}
		$date     = date( 'Y-m-d', $timestamp );
		$response = [];

		foreach ( $items as $item ) {
			$_item = $item->to_rest( $date );
			unset( $_item['logs'] );
			$_item['moving'] = ( $_item['last_log']['utc_timestamp'] + 600 ) > $timestamp;
			$response[]      = $_item;
		}

		return $response;
	}

	/**
	 * Get a collection of log items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_locations( $request ) {
		$object_id = $request->get_param( 'object_id' );
		$log_date  = $request->get_param( 'log_date' );

		if ( empty( $object_id ) ) {
			return $this->respondUnprocessableEntity();
		}

		FirebaseDatabase::sync_employees();

		$items = ( new TrackableObject() )->find_by_object_id( $object_id );

		if ( ! $items instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$timestamp = current_time( 'timestamp', true );

		if ( empty( $log_date ) ) {
			$log_date = date( 'Y-m-d', $timestamp );
		}

		$object           = $items->to_rest( $log_date );
		$object['moving'] = ( $object['last_log']['utc_timestamp'] + 600 ) > $timestamp;

		$snappedPoints = $this->get_snapped_points( $object['logs'], $object_id, $log_date );
		$item          = ( new TrackableObjectLog() )->find_object_log( $object_id, $log_date );

		return $this->respondOK( [
			'object'        => $object,
			'utc_timestamp' => $timestamp,
			'snappedPoints' => $snappedPoints,
			'polyline'      => $item->get_log_data_by_time_range(),
			'min_max_date'  => $items->find_min_max_log_date(),
			'idle_time'     => ( 10 * 60 ), // Ten minutes in seconds
		] );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function log_location( $request ) {
		$objects      = $request->get_param( 'objects' );
		$current_time = current_time( 'timestamp', true );

		$item = [];
		foreach ( $objects as $object ) {
			$object_id = isset( $object['Employee_ID'] ) ? $object['Employee_ID'] : null;
			if ( empty( $object_id ) ) {
				continue;
			}
			$item[ $object_id ] = [
				'latitude'      => isset( $object['latitude'] ) ? $object['latitude'] : 0,
				'longitude'     => isset( $object['longitude'] ) ? $object['longitude'] : 0,
				'online'        => isset( $object['online'] ) && $object['online'] == 'true',
				'utc_timestamp' => $current_time,
			];
		}

		TrackableObjectLog::log_objects( $item );

		return $this->respondCreated();
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_logs( $request ) {
		$per_page  = $request->get_param( 'per_page' );
		$paged     = $request->get_param( 'paged' );
		$object_id = $request->get_param( 'object_id' );
		$log_date  = $request->get_param( 'log_date' );

		$args = [];

		if ( ! empty( $object_id ) ) {
			$args['object_id'] = $object_id;
		}

		if ( ! empty( $log_date ) ) {
			$args['log_date'] = $log_date;
		}

		$_logs = ( new TrackableObjectLog() )->find( $args );

		return $this->respondOK( $_logs );
	}

	/**
	 * Get an item from collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function delete_log( $request ) {
		$id = (int) $request->get_param( 'id' );

		$trackableObject = new TrackableObjectLog();
		$object          = $trackableObject->find_by_id( $id );

		if ( ! $object instanceof TrackableObjectLog ) {
			return $this->respondNotFound();
		}

		if ( $trackableObject->delete( $id ) ) {
			return $this->respondOK( [ 'id' => $id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Get snapped points
	 *
	 * @param array $logs
	 * @param string $object_id
	 * @param string $log_date
	 *
	 * @return array
	 */
	private function get_snapped_points( $logs, $object_id, $log_date ) {
		$transient_name       = sprintf( '_snapped_points_%s_%s', $object_id, $log_date );
		$transient_expiration = MINUTE_IN_SECONDS;
		$points               = get_transient( $transient_name );

		if ( false === $points ) {

			if ( count( $logs ) <= 100 ) {
				$points = $this->get_snapped_points_for_chunk( $logs );
				set_transient( $transient_name, $points, $transient_expiration );

				return $points;
			}

			$chunks = array_chunk( $logs, 100 );
			$points = [];

			foreach ( $chunks as $chunk ) {
				$points = array_merge( $points, $this->get_snapped_points_for_chunk( $chunk ) );
			}

			set_transient( $transient_name, $points, $transient_expiration );
		}

		return $points;
	}

	/**
	 * Get only hundred logs
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private function get_snapped_points_for_chunk( $logs ) {
		$path = [];
		foreach ( $logs as $log ) {
			if ( empty( $log['latitude'] ) || empty( $log['longitude'] ) ) {
				continue;
			}
			$path[] = sprintf( "%s,%s", $log['latitude'], $log['longitude'] );
		}

		$url = add_query_arg( [
			'key'         => Settings::get_map_api_key(),
			'path'        => implode( "|", $path ),
			'interpolate' => true,
		], 'https://roads.googleapis.com/v1/snapToRoads' );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body    = wp_remote_retrieve_body( $response );
		$objects = json_decode( $body, true );

		return isset( $objects['snappedPoints'] ) ? $objects['snappedPoints'] : [];
	}
}
