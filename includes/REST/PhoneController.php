<?php

namespace Stackonet\REST;

use Stackonet\Models\Phone;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class PhoneController extends ApiController {
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
		register_rest_route( $this->namespace, '/phones', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ] ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
		] );
	}

	/**
	 * Retrieves a collection of devices.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$author = (int) $request->get_param( 'author' );
		if ( ! $author ) {
			$author = get_current_user_id();
		}

		$phone  = new Phone();
		$phones = $phone->find( [ 'created_by' => $author, ] );

		$response = [ 'items' => $phones ];

		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$params = $request->get_params();
		if ( isset( $params['issues'] ) && is_array( $params['issues'] ) ) {
			$params['issues'] = maybe_serialize( $params['issues'] );
		}

		$phone = new Phone();
		$id    = $phone->create( $params );

		if ( ! $id ) {
			return $this->respondInternalServerError();
		}

		$phone = $phone->find_by_id( $id );

		return $this->respondCreated( $phone );
	}

}
