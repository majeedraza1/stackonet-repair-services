<?php

namespace Stackonet;

use Stackonet\Models\ServiceArea;

defined( 'ABSPATH' ) || exit;

class Ajax {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_ajax_get_services_areas', [ self::$instance, 'get_services_areas' ] );
			add_action( 'wp_ajax_create_service_area', [ self::$instance, 'create_service_area' ] );
		}

		return self::$instance;
	}

	/**
	 * Get service areas
	 */
	public static function get_services_areas() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new service area.', 401 );
		}

		$data = ServiceArea::get_areas();

		wp_send_json_success( $data, 201 );
	}

	/**
	 * Create new service area
	 */
	public function create_service_area() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new service area.', 401 );
		}

		$zip_code = isset( $_POST['zip_code'] ) ? sanitize_text_field( $_POST['zip_code'] ) : '';
		$address  = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';

		if ( empty( $zip_code ) ) {
			wp_send_json_error( 'Zip code is required.' );
		}

		$data = ServiceArea::create( [
			'zip_code' => $zip_code,
			'address'  => $address,
		] );

		wp_send_json_success( $data, 200 );
	}
}
