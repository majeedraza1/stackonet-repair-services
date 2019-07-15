<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Models\CarrierStore;
use Stackonet\Modules\SupportTicket\CarrierStoreToSupportTicket;
use Stackonet\Supports\Logger;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CarrierStoreController extends ApiController {
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
		register_rest_route( $this->namespace, '/carrier-store', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
		] );

		register_rest_route( $this->namespace, '/carrier-store/delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/carrier-store/batch_delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_items' ] ],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$status   = $request->get_param( 'status' );
		$per_page = $request->get_param( 'per_page' );
		$paged    = $request->get_param( 'paged' );

		$survey = new CarrierStore();

		$status   = ! empty( $status ) ? $status : 'all';
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged    = ! empty( $paged ) ? absint( $paged ) : 1;

		$items      = $survey->find( [ 'status' => $status, 'paged' => $paged, 'per_page' => $per_page, ] );
		$counts     = $survey->count_records();
		$pagination = $survey->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		$response = [ 'items' => $items, 'counts' => $counts, 'pagination' => $pagination ];

		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$device_status = $request->get_param( 'device_status' );
		$latitude      = $request->get_param( 'latitude' );
		$longitude     = $request->get_param( 'longitude' );
		$full_address  = $request->get_param( 'full_address' );
		$address       = $request->get_param( 'address' );
		$brand         = $request->get_param( 'brand' );
		$gadget        = $request->get_param( 'gadget' );
		$model         = $request->get_param( 'model' );
		$images_ids    = $request->get_param( 'images_ids' );
		$tips_amount   = $request->get_param( 'tips_amount' );
		$email         = $request->get_param( 'email' );
		$phone         = $request->get_param( 'phone' );
		$industry      = $request->get_param( 'industry' );
		$store         = $request->get_param( 'store' );
		$name          = $request->get_param( 'name' );

		$survey = new CarrierStore();
		$id     = $survey->create( [
			'brand'         => $brand,
			'gadget'        => $gadget,
			'model'         => $model,
			'images_ids'    => $images_ids,
			'latitude'      => $latitude,
			'longitude'     => $longitude,
			'full_address'  => $full_address,
			'address'       => $address,
			'device_status' => $device_status,
			'tips_amount'   => $tips_amount,
			'email'         => $email,
			'phone'         => $phone,
			'industry'      => $industry,
			'store'         => $store,
			'name'          => $name,
		] );

		if ( $id ) {
			$response = $survey->find_by_id( $id );
			try {
				CarrierStoreToSupportTicket::process( $response );
			} catch ( Exception $e ) {
				Logger::log( $e->getMessage() );
			}

			return $this->respondOK( $response );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}
		$id     = $request->get_param( 'id' );
		$action = $request->get_param( 'action' );

		$id     = ! empty( $id ) ? absint( $id ) : 0;
		$action = ! empty( $action ) ? sanitize_text_field( $action ) : '';

		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			return $this->respondUnauthorized();
		}

		$class  = new CarrierStore();
		$survey = $class->find_by_id( $id );

		if ( ! $survey instanceof CarrierStore ) {
			return $this->respondNotFound();
		}

		if ( 'trash' == $action ) {
			$class->trash( $id );
		}
		if ( 'restore' == $action ) {
			$class->restore( $id );
		}
		if ( 'delete' == $action ) {
			$class->delete( $id );
		}

		return $this->respondOK( "#{$id} Survey record has been deleted" );
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_items( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$ids    = $request->get_param( 'ids' );
		$ids    = is_array( $ids ) ? array_map( 'intval', $ids ) : [];
		$action = $request->get_param( 'action' );
		$action = ! empty( $action ) ? sanitize_text_field( $action ) : '';

		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			return $this->respondUnprocessableEntity();
		}

		$class = new CarrierStore();

		foreach ( $ids as $id ) {
			if ( 'trash' == $action ) {
				$class->trash( $id );
			}
			if ( 'restore' == $action ) {
				$class->restore( $id );
			}
			if ( 'delete' == $action ) {
				$class->delete( $id );
			}
		}

		return $this->respondOK( "Phones has been deleted" );
	}
}
