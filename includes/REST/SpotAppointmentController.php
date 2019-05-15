<?php

namespace Stackonet\REST;

use Stackonet\Models\Appointment;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SpotAppointmentController extends ApiController {

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
		register_rest_route( $this->namespace, '/spot-appointment', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
		] );

		register_rest_route( $this->namespace, '/spot-appointment/delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/spot-appointment/batch_delete', [
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

		$appointment = new Appointment();

		$status   = ! empty( $status ) ? $status : 'all';
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged    = ! empty( $paged ) ? absint( $paged ) : 1;

		$items      = $appointment->find( [ 'status' => $status, 'paged' => $paged, 'per_page' => $per_page, ] );
		$counts     = $appointment->count_records();
		$pagination = $appointment->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );;

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

		$appointment = new Appointment();
		$id          = $appointment->create( $request->get_params() );

		if ( $id ) {
			$response = $appointment->find_by_id( $id );

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

		$class  = new Appointment();
		$survey = $class->find_by_id( $id );

		if ( ! $survey instanceof Appointment ) {
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

		$class = new Appointment();

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
