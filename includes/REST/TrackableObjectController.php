<?php

namespace Stackonet\REST;

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
		$items = ( new TrackableObject() )->find();

		return $this->respondOK( [ 'items' => $items, 'counts' => [], 'pagination' => [] ] );
	}

	/**
	 * Get a collection of log items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_locations( $request ) {
		$items = ( new TrackableObjectLog() )->find();

		return $this->respondOK( [ 'items' => $items, 'counts' => [], 'pagination' => [] ] );
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
		$current_time = current_time( 'timestamp' );

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
}
