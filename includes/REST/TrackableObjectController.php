<?php

namespace Stackonet\REST;

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

		$items = ( new TrackableObject() )->find_by_object_id( $object_id );

		if ( ! $items instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$timestamp = current_time( 'timestamp', true );

		if ( empty( $log_date ) ) {
			$log_date = date( 'Y-m-d', $timestamp );
		}

		$object          = $items->to_rest( $log_date );
		$_item['moving'] = ( $object['last_log']['utc_timestamp'] + 600 ) > $timestamp;

		$snappedPoints = $this->get_snapped_points( $object['logs'] );

		return $this->respondOK( [
			'object'        => $object,
			'utc_timestamp' => $timestamp,
			'snappedPoints' => $snappedPoints,
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
				'latitude'      => isset( $object['latitude'] ) ? $object['latitude'] : null,
				'longitude'     => isset( $object['longitude'] ) ? $object['longitude'] : null,
				'online'        => isset( $object['online'] ) && $object['online'] == 'true',
				'utc_timestamp' => $current_time,
			];
		}

		TrackableObjectLog::log_objects( $item );

		return $this->respondCreated();
	}

	/**
	 * Get snapped points
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	private function get_snapped_points( $logs ) {
		if ( count( $logs ) > 100 ) {
			$logs = $this->get_hundred_path( $logs );
		}
		$path = [];
		foreach ( $logs as $log ) {
			$path[] = sprintf( "%s,%s", $log['latitude'], $log['longitude'] );
		}

		$url = add_query_arg( [
			'key'         => Settings::get_map_api_key(),
			'path'        => implode( "|", $path ),
			'interpolate' => false,
		], 'https://roads.googleapis.com/v1/snapToRoads' );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body    = wp_remote_retrieve_body( $response );
		$objects = json_decode( $body, true );

		return isset( $objects['snappedPoints'] ) ? $objects['snappedPoints'] : [];
	}

	/**
	 * Get only hundred logs
	 *
	 * @param array $logs
	 *
	 * @return mixed
	 */
	private function get_hundred_path( $logs ) {
		return $logs;
	}
}
