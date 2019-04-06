<?php

namespace Stackonet\REST;

use Stackonet\Models\Phone;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TrackStatusController extends ApiController {
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
		register_rest_route( $this->namespace, '/track-status', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
		] );
	}

	/**
	 * Retrieves a collection of old items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$user_id = get_current_user_id();

		$type     = $request->get_param( 'type' );
		$per_page = $request->get_param( 'per_page' );
		$paged    = $request->get_param( 'paged' );

		$type     = in_array( $type, [ 'new', 'old' ] ) ? $type : 'old';
		$per_page = is_numeric( $per_page ) ? absint( $per_page ) : 20;
		$paged    = is_numeric( $paged ) ? absint( $paged ) : 1;

		$args = [ 'created_by' => $user_id, 'per_page' => $per_page, 'paged' => $paged ];

		if ( 'new' == $type ) {
			$items = Phone::findNew( $args );
		} else {
			$items = Phone::findOld( $args );
		}

		$response = [ 'items' => $items ];

		return $this->respondOK( $response );
	}
}
