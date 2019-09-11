<?php

namespace Stackonet\REST;

use Stackonet\Integrations\FirebaseDatabase;
use Stackonet\Integrations\GoogleMap;
use Stackonet\Models\TrackableObject;
use Stackonet\Supports\ArrayHelper;
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

		register_rest_route( $this->namespace, '/trackable-objects/batch', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'update_batch_items' ],
				'args'     => $this->get_batch_params(),
			],
		] );

		register_rest_route( $this->namespace, '/trackable-objects/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_item' ] ],
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_item' ] ],
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
		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$status   = $request->get_param( 'status' );
		$status   = ( 'trash' == $status ) ? $status : 'all';

		FirebaseDatabase::sync_employees();

		$timestamp = current_time( 'timestamp' );
		$items     = ( new TrackableObject )->find( [
			'per_page' => $per_page,
			'page'     => $page,
			'status'   => $status,
		] );
		$items     = $this->prepare_items_for_response( $items, $request );

		$user_id           = get_current_user_id();
		$transient_name    = 'previous_objects_' . $user_id;
		$previous_response = get_transient( $transient_name );
		$previous_response = is_array( $previous_response ) ? $previous_response : [];

		set_transient( $transient_name, $items, HOUR_IN_SECONDS );

		$diff   = ArrayHelper::array_diff_recursive( $previous_response, $items );
		$counts = ( new TrackableObject() )->count_records();

		$data = [
			'utc_timestamp' => $timestamp,
			'idle_time'     => ( 10 * 60 ), // Ten minutes in seconds
			'items'         => $items,
			'is_changed'    => count( $diff ) > 0,
			'counts'        => $counts,
			'pagination'    => self::get_pagination_data( $counts[ $status ], $per_page, $page )
		];

		$data['statuses'] = [
			[ 'key' => 'all', 'label' => 'All' ],
			[ 'key' => 'trash', 'label' => 'Trash' ],
		];

		foreach ( $data['statuses'] as $index => $_status ) {
			$data['statuses'][ $index ]['count']  = isset( $counts[ $_status['key'] ] ) ? $counts[ $_status['key'] ] : 0;
			$data['statuses'][ $index ]['active'] = ( $_status['key'] == $status );
		}

		return $this->respondOK( $data );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$object_id = $request->get_param( 'id' );

		$object = ( new TrackableObject() )->find_by_id( $object_id );

		return $this->respondOK($object);
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
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_batch_items( $request ) {
		$trash   = $request->get_param( 'trash' );
		$restore = $request->get_param( 'restore' );
		$delete  = $request->get_param( 'delete' );

		$TrackableObject = new TrackableObject;

		if ( count( $trash ) ) {
			$ids = array_map( 'intval', $trash );
			foreach ( $ids as $id ) {
				$TrackableObject->trash( $id );
			}
		}

		if ( count( $restore ) ) {
			$ids = array_map( 'intval', $restore );
			foreach ( $ids as $id ) {
				$TrackableObject->restore( $id );
			}
		}

		if ( count( $delete ) ) {
			$ids = array_map( 'intval', $delete );
			foreach ( $ids as $id ) {
				$TrackableObject->delete( $id );
			}
		}

		return $this->respondOK();
	}

	/**
	 * @param TrackableObject[] $items
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function prepare_items_for_response( $items, $request ) {
		$response = [];
		foreach ( $items as $item ) {
			$response[] = $this->prepare_item_for_response( $item, $request )->get_data();
		}

		return $response;
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
	 * Get the query params for batch.
	 *
	 * @return array
	 */
	public function get_batch_params() {
		return array(
			'trash'   => array(
				'description'       => 'List of items ids to be trashed.',
				'type'              => 'array',
				'default'           => [],
				'validate_callback' => 'rest_validate_request_arg',
			),
			'restore' => array(
				'description'       => 'List of items ids to be restored.',
				'type'              => 'array',
				'default'           => [],
				'validate_callback' => 'rest_validate_request_arg',
			),
			'delete'  => array(
				'description'       => 'List of items ids to be deleted.',
				'type'              => 'array',
				'default'           => [],
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}
}
