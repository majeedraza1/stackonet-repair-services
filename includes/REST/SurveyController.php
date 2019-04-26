<?php

namespace Stackonet\REST;

use Stackonet\Models\Survey;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SurveyController extends ApiController {

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
		register_rest_route( $this->namespace, '/survey', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ] ],
		] );
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

		$survey = new Survey();
		$id     = $survey->create( [
			'latitude'      => $latitude,
			'longitude'     => $longitude,
			'full_address'  => $full_address,
			'address'       => $address,
			'device_status' => $device_status,
		] );

		if ( $id ) {
			$response = $survey->find_by_id( $id );

			return $this->respondOK( $response );
		}

		return $this->respondInternalServerError();
	}
}
