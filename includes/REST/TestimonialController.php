<?php

namespace Stackonet\REST;

use Stackonet\Models\Testimonial;

defined( 'ABSPATH' ) || exit;

class TestimonialController extends ApiController {

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
		register_rest_route( $this->namespace, '/testimonials', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'args'     => $this->get_collection_params(),
			],
			[ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ], ],
		] );

		register_rest_route( $this->namespace, '/testimonials/batch/trash', [
			'methods'  => \WP_REST_Server::DELETABLE,
			'callback' => [ $this, 'trash_items' ],
		] );

		register_rest_route( $this->namespace, '/testimonials/batch/restore', [
			'methods'  => \WP_REST_Server::DELETABLE,
			'callback' => [ $this, 'restore_items' ],
		] );

		register_rest_route( $this->namespace, '/testimonials/batch/delete', [
			'methods'  => \WP_REST_Server::DELETABLE,
			'callback' => [ $this, 'delete_items' ],
		] );

		register_rest_route( $this->namespace, '/testimonials/(?P<id>\d+)', [
			[ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ $this, 'get_item' ], ],
			[ 'methods' => \WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ], ],
			[ 'methods' => \WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_item' ], ],
		] );
	}

	/**
	 * Get a collection of items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $this->respondForbidden();
		}

		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$search   = $request->get_param( 'search' );
		$order    = $request->get_param( 'order' );
		$orderby  = $request->get_param( 'orderby' );
		$status   = $request->get_param( 'status' );

		$args = array(
			'status' => $status,
		);

		if ( null !== $page ) {
			$args['paged'] = (int) $page;
		}
		if ( null !== $per_page ) {
			$args['per_page'] = (int) $per_page;
		}
		if ( null !== $order ) {
			$args['order'] = (string) $order;
		}
		if ( null !== $orderby ) {
			$args['orderby'] = (string) $orderby;
		}
		if ( null !== $search ) {
			$args['search'] = (string) $search;
		}

		$testimonials = ( new Testimonial() )->find( $args );
		$counts       = ( new Testimonial() )->count_records();

		return $this->respondOK( [ 'items' => $testimonials, 'counts' => $counts ] );
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $this->respondForbidden();
		}

		$id = (int) $request->get_param( 'id' );

		$testimonial = ( new Testimonial() )->find_by_id( $id );
		if ( ! $testimonial instanceof Testimonial ) {
			return $this->respondNotFound();
		}

		$params = $request->get_params();

		// Handle Trash Request
		if ( isset( $params['status'] ) && 'trash' == $params['status'] ) {
			$testimonial->trash();

			return $this->respondOK();
		}

		// Handle Restore Request
		if ( isset( $params['status'] ) && 'restore' == $params['status'] ) {
			$testimonial->restore();

			return $this->respondOK();
		}

		$status = ( new Testimonial() )->update( $params );
		if ( $status ) {
			return $this->respondOK( $params );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $this->respondForbidden();
		}

		$id = (int) $request->get_param( 'id' );

		$testimonial = ( new Testimonial() )->find_by_id( $id );
		if ( ! $testimonial instanceof Testimonial ) {
			return $this->respondNotFound();
		}

		if ( $testimonial->delete() ) {
			return $this->respondOK( [ 'deleted' => true ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Trash a collection of items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function trash_items( $request ) {
		$ids = (array) $request->get_param( 'ids' );
		$ids = array_map( 'intval', $ids );
		foreach ( $ids as $id ) {
			( new Testimonial() )->trash( $id );
		}

		return $this->respondOK();
	}

	/**
	 * Restore a collection of items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function restore_items( $request ) {
		$ids = (array) $request->get_param( 'ids' );
		$ids = array_map( 'intval', $ids );
		foreach ( $ids as $id ) {
			( new Testimonial() )->restore( $id );
		}

		return $this->respondOK();
	}

	/**
	 * Delete a collection of items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_items( $request ) {
		$ids = (array) $request->get_param( 'ids' );
		$ids = array_map( 'intval', $ids );
		foreach ( $ids as $id ) {
			( new Testimonial() )->delete( $id );
		}

		return $this->respondOK();
	}
}
