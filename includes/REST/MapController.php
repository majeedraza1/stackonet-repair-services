<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use Stackonet\Models\Map;
use Stackonet\Modules\SupportTicket\MapToSupportTicket;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class MapController extends ApiController {


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
		register_rest_route( $this->namespace, '/map', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
		] );
		register_rest_route( $this->namespace, '/map/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
		] );
		register_rest_route( $this->namespace, '/map/(?P<id>\d+)/support-ticket', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_support_ticket' ] ],
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

		$status   = $request->get_param( 'status' );
		$per_page = $request->get_param( 'per_page' );
		$paged    = $request->get_param( 'paged' );

		$map = new Map();

		$status   = ! empty( $status ) ? $status : 'all';
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged    = ! empty( $paged ) ? absint( $paged ) : 1;

		$items      = $map->find( [ 'status' => $status, 'paged' => $paged, 'per_page' => $per_page, ] );
		$counts     = $map->count_records();
		$pagination = $map->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		$response = [ 'items' => $items, 'counts' => $counts, 'pagination' => $pagination ];

		return $this->respondOK( $response );
	}

	/**
	 * Create one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 * @throws Exception
	 */
	public function create_item( $request ) {
		$data = $this->prepare_item_for_database( $request );

		$id = ( new Map() )->create( $data );

		if ( $id ) {
			$item = ( new Map() )->find_by_id( $id );

			return $this->respondCreated( $item );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );

		$map  = new Map();
		$item = $map->find_by_id( $id );

		if ( ! $item instanceof Map ) {
			return $this->respondNotFound();
		}

		if ( $map->update( $request->get_params() ) ) {
			return $this->respondOK();
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_support_ticket( $request ) {
		$id = $request->get_param( 'id' );

		$map  = new Map();
		$item = $map->find_by_id( $id );

		if ( ! $item instanceof Map ) {
			return $this->respondNotFound();
		}

		try {
			MapToSupportTicket::process( $item );
			$item = $map->find_by_id( $id );

			return $this->respondCreated( $item );
		} catch ( Exception $e ) {
			return $this->respondUnprocessableEntity( $e->getMessage() );
		}
	}

	/**
	 * Prepare the item for create or update operation.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $prepared_item
	 * @throws Exception
	 */
	public function prepare_item_for_database( $request ) {
		$dateTime = self::formatDate( $request->get_param( 'base_datetime' ), 'Y-m-d H:i:s' );

		$prepared_item = [
			'title'                  => $request->get_param( 'title' ),
			'formatted_base_address' => $request->get_param( 'formatted_base_address' ),
			'base_address_latitude'  => $request->get_param( 'base_address_latitude' ),
			'base_address_longitude' => $request->get_param( 'base_address_longitude' ),
			'place_text'             => $request->get_param( 'place_text' ),
			'travel_mode'            => $request->get_param( 'travel_mode' ),
			'places'                 => $request->get_param( 'places' ),
			'base_datetime'          => $dateTime,
		];

		return $prepared_item;
	}
}
