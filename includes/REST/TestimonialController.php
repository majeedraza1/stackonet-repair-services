<?php

namespace Stackonet\REST;


use Stackonet\Models\Testimonial;

class TestimonialController extends ApiController {

	private static $instance;

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
		register_rest_route( $this->namespace, '/testimonials', array(
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
				'args'     => $this->get_collection_params(),
			),
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_item' ),
			),
		) );

		register_rest_route( $this->namespace, '/testimonials/(?P<id>\d+)', array(
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
			),
			array(
				'methods'  => \WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'update_item' ),
			),
			array(
				'methods'  => \WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
			),
		) );
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

		$args     = array();
		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$search   = $request->get_param( 'search' );
		$order    = $request->get_param( 'order' );
		$orderby  = $request->get_param( 'orderby' );

		if ( null !== $page ) {
			$args['page'] = (int) $page;
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

		return $this->respondOK( $testimonials );
	}
}
