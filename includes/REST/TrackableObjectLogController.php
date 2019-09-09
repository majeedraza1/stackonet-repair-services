<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Integrations\FirebaseDatabase;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Models\TrackableObject;
use Stackonet\Models\TrackableObjectLog;
use Stackonet\Models\TrackableObjectTimeline;
use Stackonet\Supports\ArrayHelper;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TrackableObjectLogController extends ApiController {

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
		register_rest_route( $this->namespace, '/trackable-objects/logs', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'args'     => $this->get_collection_params(),
			],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/logs/(?P<id>\d+)', [
			[
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => [ $this, 'delete_item' ]
			],
		] );
	}


	/**
	 * Get a collection of log items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 * @throws Exception
	 */
	public function get_items( $request ) {
		$object_id       = $request->get_param( 'object_id' );
		$log_date        = $request->get_param( 'log_date' );
		$snapToRoads     = $request->get_param( 'snap_to_roads' );
		$includePolyline = $request->get_param( 'polyline' );
		$includeTimeline = $request->get_param( 'timeline' );

		FirebaseDatabase::sync_employees();

		$object = ( new TrackableObject() )->find_by_object_id( $object_id );

		if ( ! $object instanceof TrackableObject ) {
			return $this->respondNotFound();
		}

		$timestamp = current_time( 'timestamp' );

		if ( empty( $log_date ) ) {
			$log_date = date( 'Y-m-d', $timestamp );
		}

		$objectArray = $this->prepare_item_for_response( $object, $request )->get_data();

		$transient_name    = 'previous_object_' . md5( wp_json_encode( [
				'object_id'   => $object_id,
				'log_date'    => $log_date,
				'snapToRoads' => $snapToRoads,
				'polyline'    => $includePolyline,
				'user_id'     => get_current_user_id(),
			] ) );
		$previous_response = get_transient( $transient_name );
		$previous_response = is_array( $previous_response ) ? $previous_response : [];

		set_transient( $transient_name, $objectArray, HOUR_IN_SECONDS );

		$diff = ArrayHelper::array_diff_recursive( $previous_response, $objectArray );

		$response = [
			'object'        => $objectArray,
			'is_changed'    => count( $diff ) > 0,
			'utc_timestamp' => $timestamp,
			'polyline'      => [],
			'snappedPoints' => [],
			'timeline'      => [],
			'min_max_date'  => $object->find_min_max_log_date(),
			'idle_time'     => ( 10 * 60 ), // Ten minutes in seconds
		];

		if ( $includePolyline || $snapToRoads || $includeTimeline ) {
			$item = ( new TrackableObjectLog() )->find_object_log( $object_id, $log_date );

			if ( $item instanceof TrackableObjectLog ) {
				if ( $includePolyline ) {
					$response['polyline'] = $item->get_log_data_by_time_range();
				}

				if ( $includeTimeline ) {
					$logs                 = $item->get_log_data();
					$logs                 = TrackableObjectTimeline::format_timeline_from_logs( $logs, $object_id, $log_date );
					$response['timeline'] = TrackableObjectTimeline::format_timeline_for_rest( $logs );
				}

				if ( $snapToRoads && count( $response['polyline'] ) ) {
					$response['snappedPoints'] = $item->get_snapped_points_by_time_range();
				}
			}
		}

		return $this->respondOK( $response );
	}

	/**
	 * Get an item from collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
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
	 * @param TrackableObject $trackable_object
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $trackable_object, $request ) {
		$timestamp = current_time( 'timestamp' );
		$date      = date( 'Y-m-d', $timestamp );
		$item      = $trackable_object->to_rest( $date );

		unset( $item['logs'] );
		unset( $item['last_log'] );

		if ( ! empty( $item['place_id'] ) ) {
			$address                   = GoogleMap::get_address_from_place_id( $item['place_id'] );
			$item['formatted_address'] = $address['formatted_address'];
		} else if ( ! empty( $_item['latitude'] ) && ! empty( $_item['longitude'] ) ) {
			$address                   = GoogleMap::get_address_from_lat_lng( $item['latitude'], $item['longitude'] );
			$item['formatted_address'] = $address['formatted_address'];
		}

		$item['moving'] = ( $item['utc_timestamp'] + 600 ) > $timestamp;

		return new WP_REST_Response( $item );
	}

	/**
	 * Retrieves the query params for the timeline.
	 *
	 * @return array Query parameters for the timeline.
	 */
	public function get_collection_params() {
		return [
			'object_id'     => [
				'description'       => 'Object id',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'log_date'      => [
				'description'       => 'Log date',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'snap_to_roads' => [
				'description'       => 'Include Google SnapToRoads API Data',
				'type'              => 'boolean',
				'default'           => false,
				'validate_callback' => 'rest_validate_request_arg',
			],
			'polyline'      => [
				'description'       => 'Include polyline data',
				'type'              => 'boolean',
				'default'           => true,
				'validate_callback' => 'rest_validate_request_arg',
			],
			'timeline'      => [
				'description'       => 'Include timeline data',
				'type'              => 'boolean',
				'default'           => false,
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}
