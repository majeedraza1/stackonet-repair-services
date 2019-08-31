<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Integrations\FirebaseDatabase;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Models\TrackableObject;
use Stackonet\Models\TrackableObjectLog;
use Stackonet\Models\TrackableObjectTimeline;
use Stackonet\Supports\DistanceCalculator;
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
	 * @param array $_logs
	 * @param $object_id
	 * @param $log_date
	 *
	 * @return array|mixed
	 */
	public static function get_object_timeline( array $_logs, $object_id, $log_date ) {
		$total_logs = count( $_logs );

		$transient_name = sprintf( "object_time_line_%s_%s", $object_id, $log_date );
		$logs           = get_transient( $transient_name );
		if ( false !== $logs ) {
			return $logs;
		}

		$logs = [];
		foreach ( $_logs as $index => $log ) {
			$logs[ $index ] = $log;

			if ( $index < ( $total_logs - 1 ) ) {
				$next_log = $_logs[ $index + 1 ];

				$logs[ $index ]['duration'] = [
					'value' => ( intval( $next_log['utc_timestamp'] ) - intval( $log['utc_timestamp'] ) ),
					'unit'  => 'seconds'
				];
			} else {
				$logs[ $index ]['duration'] = [
					'value' => intval( current_time( 'timestamp' ) - $log['utc_timestamp'] ),
					'unit'  => 'seconds'
				];
			}

			$should_get_address = ! in_array( 'street_address', $log['address_types'] );
			if ( $index == 0 || $index == ( $total_logs - 1 ) ) {
				$should_get_address = true;
			}

			if ( $should_get_address ) {
				$address = GoogleMap::get_address_from_place_id( $log['place_id'] );

				$logs[ $index ]['address'] = [
					'name'              => $address['name'],
					'icon'              => $address['icon'],
					'formatted_address' => $address['formatted_address'],
				];
			}
		}

		$final_logs          = [];
		$last_place          = [];
		$temp_street_address = [];

		foreach ( $logs as $index => $log ) {
			$has_address = isset( $log['address'] );

			if ( ! $has_address ) {
				$temp_street_address[] = $log;
			}

			if ( $has_address ) {

				if ( ! empty( $last_place ) ) {
					$current_name = ! empty( $log['address']['name'] ) ? $log['address']['name'] : null;
					$last_name    = ! empty( $last_place['address']['name'] ) ? $last_place['address']['name'] : null;


					if ( ! is_null( $last_name ) && $current_name == $last_name ) {
						$temp_street_address = [];
					} else {
						$final_logs[] = $log;
					}
				}

				$last_place = $log;
			}
		}

		set_transient( $transient_name, $final_logs, MINUTE_IN_SECONDS );

		return $final_logs;
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

		register_rest_route( $this->namespace, '/trackable-objects/timeline', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_timeline' ],
				'args'     => $this->get_timeline_params(),
			],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/logs', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_logs' ] ],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/logs/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_log' ] ],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/log', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_locations' ] ],
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

		$timestamp = current_time( 'timestamp' );
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
			$timestamp = current_time( 'timestamp' );
		}
		$date     = date( 'Y-m-d', $timestamp );
		$response = [];

		foreach ( $items as $item ) {
			$_item = $item->to_rest( $date );
			if ( empty( $_item['last_log']['latitude'] ) || empty( $_item['last_log']['longitude'] ) ) {
				continue;
			}
			unset( $_item['logs'] );
			$_item['moving'] = ( $_item['last_log']['utc_timestamp'] + 600 ) > $timestamp;

			if ( ! empty( $_item['last_log']['latitude'] ) && ! empty( $_item['last_log']['longitude'] ) ) {
				$address                                = GoogleMap::get_address_from_lat_lng(
					$_item['last_log']['latitude'],
					$_item['last_log']['longitude']
				);
				$_item['last_log']['address']           = $address;
				$_item['last_log']['formatted_address'] = $address['formatted_address'];
			}

			$response[] = $_item;
		}

		return $response;
	}

	/**
	 * Get a collection of log items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 * @throws Exception
	 */
	public function get_locations( $request ) {
		$object_id   = $request->get_param( 'object_id' );
		$log_date    = $request->get_param( 'log_date' );
		$snapToRoads = $request->get_param( 'snapToRoads' );
		$snapToRoads = in_array( $snapToRoads, [ 1, true, 'true' ], true );

		if ( empty( $object_id ) ) {
			return $this->respondUnprocessableEntity();
		}

		FirebaseDatabase::sync_employees();

		$items = ( new TrackableObject() )->find_by_object_id( $object_id );

		if ( ! $items instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$timestamp = current_time( 'timestamp' );

		if ( empty( $log_date ) ) {
			$log_date = date( 'Y-m-d', $timestamp );
		}

		$object           = $items->to_rest( $log_date );
		$object['moving'] = ( $object['last_log']['utc_timestamp'] + 600 ) > $timestamp;

		$item = ( new TrackableObjectLog() )->find_object_log( $object_id, $log_date );
		if ( ! $item instanceof TrackableObjectLog ) {
			return $this->respondNotFound();
		}

		$response = [
			'object'        => $object,
			'utc_timestamp' => $timestamp,
			'polyline'      => $item->get_log_data_by_time_range(),
			'snappedPoints' => [],
			'min_max_date'  => $items->find_min_max_log_date(),
			'idle_time'     => ( 10 * 60 ), // Ten minutes in seconds
		];

		if ( $snapToRoads ) {
			$response['snappedPoints'] = $item->get_snapped_points_by_time_range();
		}

		return $this->respondOK( $response );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_logs( $request ) {
		$object_id = $request->get_param( 'object_id' );
		$log_date  = $request->get_param( 'log_date' );

		$args = [];

		if ( ! empty( $object_id ) ) {
			$args['object_id'] = $object_id;
		}

		if ( ! empty( $log_date ) ) {
			$args['log_date'] = $log_date;
		}

		$logs = ( new TrackableObjectLog() )->find( $args );

		return $this->respondOK( $logs );
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
	 * Get object timeline
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_timeline( $request ) {
		$object_id = $request->get_param( 'object_id' );
		$log_date  = $request->get_param( 'log_date' );

		if ( empty( $log_date ) ) {
			$log_date = date( "Y-m-d", current_time( 'timestamp' ) );
		}

		$object = ( new TrackableObject() )->find_by_object_id( $object_id );

		if ( ! $object instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$response = [
			'id'          => intval( $object->get( 'id' ) ),
			'object_id'   => $object->get( 'object_id' ),
			'object_name' => $object->get( 'object_name' ),
			'icon_url'    => $object->get_object_icon(),
		];

		$log   = ( new TrackableObjectLog() )->find_object_log( $object_id, $log_date );
		$_logs = $log->get_log_data();

		$logs             = TrackableObjectTimeline::format_timeline_from_logs( $_logs, $object_id, $log_date );
		$response['logs'] = TrackableObjectTimeline::format_timeline_for_rest( $logs );

		return $this->respondOK( $response );
	}

	/**
	 * Retrieves the query params for the timeline.
	 *
	 * @return array Query parameters for the timeline.
	 */
	public function get_timeline_params() {
		return [
			'context'   => $this->get_context_param(),
			'object_id' => [
				'description'       => 'Object id',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'log_date'  => [
				'description'       => 'Log date',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}
