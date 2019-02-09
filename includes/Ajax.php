<?php

namespace Stackonet;

use Stackonet\Models\Device;
use Stackonet\Models\DeviceIssue;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;

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

			// Service Area
			add_action( 'wp_ajax_get_services_areas', [ self::$instance, 'get_services_areas' ] );
			add_action( 'wp_ajax_create_service_area', [ self::$instance, 'create_service_area' ] );
			// Device Issue
			add_action( 'wp_ajax_get_device_issues', [ self::$instance, 'get_device_issues' ] );
			add_action( 'wp_ajax_create_device_issue', [ self::$instance, 'create_device_issue' ] );
			// Projects
			add_action( 'wp_ajax_get_woocommerce_products', [ self::$instance, 'get_woocommerce_products' ] );
			// Device
			add_action( 'wp_ajax_get_devices', [ self::$instance, 'get_devices' ] );
			add_action( 'wp_ajax_create_device', [ self::$instance, 'create_device' ] );
			// Settings
			add_action( 'wp_ajax_update_repair_services_settings', [ self::$instance, 'update_settings' ] );
		}

		return self::$instance;
	}

	/**
	 * Update Settings
	 */
	public function update_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view devices.', 401 );
		}

		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : [];
		$data     = Settings::update( $settings );

		wp_send_json_success( $data, 200 );
	}

	/**
	 * Get devices
	 */
	public function get_devices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view devices.', 401 );
		}

		$data = Device::get_devices();

		wp_send_json_success( $data, 200 );
	}

	/**
	 * Create device
	 */
	public function create_device() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new service area.', 401 );
		}

		$defaults = array(
			'product_id'          => 0,
			'device_title'        => '',
			'device_image'        => '',
			'broken_screen_label' => '',
			'broken_screen_price' => '',
			'device_models'       => [],
			'multi_issues'        => [],
			'no_issues'           => [],
		);
		$data     = [];
		foreach ( $defaults as $key => $default ) {
			$data[ $key ] = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
		}

		$response = Device::create( $data );

		wp_send_json_success( $response, 201 );
	}

	/**
	 * Get service areas
	 */
	public static function get_services_areas() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new service area.', 401 );
		}

		$data = ServiceArea::get_areas();

		wp_send_json_success( $data, 200 );
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

		wp_send_json_success( $data, 201 );
	}

	/**
	 * Get device issues
	 */
	public static function get_device_issues() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view issues.', 401 );
		}

		$data = DeviceIssue::get_issues();

		wp_send_json_success( $data, 200 );
	}

	/**
	 * Create new device issue
	 */
	public function create_device_issue() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new device issue.', 401 );
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$price = isset( $_POST['price'] ) ? sanitize_text_field( $_POST['price'] ) : '';

		if ( empty( $title ) ) {
			wp_send_json_error( 'Issue title is required.' );
		}

		$data = DeviceIssue::create( [
			'title' => $title,
			'price' => $price,
		] );

		wp_send_json_success( $data, 201 );
	}

	/**
	 * Get WooCommerce Products
	 */
	public function get_woocommerce_products() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view products.', 401 );
		}

		$args = array(
			'limit'   => - 1,
			'orderby' => 'title',
			'order'   => 'ASC',
		);
		/** @var \WC_Product[] $products */
		$products = wc_get_products( $args );

		$response = array();
		foreach ( $products as $product ) {
			$response[] = [
				'value' => $product->get_id(),
				'label' => $product->get_title(),
			];
		}

		wp_send_json_success( $response, 200 );
	}
}
