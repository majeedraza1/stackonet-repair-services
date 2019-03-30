<?php

namespace Stackonet;

use Stackonet\Models\Device;
use Stackonet\Models\DeviceIssue;
use Stackonet\Models\OrderReminder;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;
use Stackonet\Models\Testimonial;
use Stackonet\Models\UnsupportedArea;

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
			add_action( 'wp_ajax_delete_service_area', [ self::$instance, 'delete_service_area' ] );
			// Device Issue
			add_action( 'wp_ajax_get_device_issues', [ self::$instance, 'get_device_issues' ] );
			add_action( 'wp_ajax_create_device_issue', [ self::$instance, 'create_device_issue' ] );
			add_action( 'wp_ajax_delete_device_issue', [ self::$instance, 'delete_device_issue' ] );
			// Projects
			add_action( 'wp_ajax_get_woocommerce_products', [ self::$instance, 'get_woocommerce_products' ] );
			// Device
			add_action( 'wp_ajax_get_devices', [ self::$instance, 'get_devices' ] );
			add_action( 'wp_ajax_get_device', [ self::$instance, 'get_device' ] );
			add_action( 'wp_ajax_create_device', [ self::$instance, 'create_device' ] );
			add_action( 'wp_ajax_delete_device', [ self::$instance, 'delete_device' ] );
			// Settings
			add_action( 'wp_ajax_update_repair_services_settings', [ self::$instance, 'update_settings' ] );
			// Confirm Appointment
			add_action( 'wp_ajax_confirm_appointment', [ self::$instance, 'confirm_appointment' ] );
			add_action( 'wp_ajax_nopriv_confirm_appointment', [ self::$instance, 'confirm_appointment' ] );
			// Subscript to Email list
			add_action( 'wp_ajax_create_request_areas', [ self::$instance, 'create_request_areas' ] );
			add_action( 'wp_ajax_nopriv_create_request_areas', [ self::$instance, 'create_request_areas' ] );
			add_action( 'wp_ajax_get_request_areas', [ self::$instance, 'get_request_areas' ] );
			// Testimonial
			add_action( 'wp_ajax_get_client_testimonials', [ self::$instance, 'get_client_testimonials' ] );
			add_action( 'wp_ajax_accept_reject_testimonial', [ self::$instance, 'accept_reject_testimonial' ] );
			add_action( 'wp_ajax_create_client_testimonial', [ self::$instance, 'create_client_testimonial' ] );
			add_action( 'wp_ajax_nopriv_create_client_testimonial', [ self::$instance, 'create_client_testimonial' ] );

			add_action( 'wp_ajax_stackonet_test', [ self::$instance, 'stackonet_test' ] );
		}

		return self::$instance;
	}

	public function stackonet_test() {
		die();
	}

	/**
	 * Get client testimonial
	 */
	public function get_client_testimonials() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view devices.', 401 );
		}

		$testimonial = new Testimonial();
		$data        = $testimonial->find();

		wp_send_json_success( $data, 200 );
	}

	public static function accept_reject_testimonial() {
		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : null;

		if ( ! in_array( $status, [ 'accept', 'reject' ] ) ) {
			wp_send_json_error( 'Invalid status.', 422 );
		}

		$testimonial = new Testimonial();
		$data        = $testimonial->find_by_id( $id );

		if ( ! $data instanceof Testimonial ) {
			wp_send_json_error( 'Testimonial not found', 404 );
		}


		$response = $testimonial->update( [
			'id'     => $data->get( 'id' ),
			'status' => $status,
		] );

		if ( $response ) {
			wp_send_json_success( 'Testimonial has been updated', 200 );
		}

		wp_send_json_error( null, 500 );
	}

	/**
	 * Create client testimonial
	 */
	public function create_client_testimonial() {
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email       = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$rating      = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$description = isset( $_POST['description'] ) ? wp_strip_all_tags( $_POST['description'] ) : '';
		$description = stripslashes( $description );

		$error = new \WP_Error();

		if ( $rating < 1 ) {
			$error->add( 'rating', 'Choose rating from 1 to 5.' );
		}

		if ( empty( $name ) ) {
			$error->add( 'full_name', 'Name is required.' );
		}

		if ( empty( $email ) ) {
			$error->add( 'email', 'Email is required.' );
		}

		if ( empty( $description ) ) {
			$error->add( 'description', 'Description is required.' );
		}

		if ( ! empty( $error->errors ) ) {
			wp_send_json_error( $error->errors, 422 );
		}

		$id = ( new Testimonial() )->create( [
			'name'        => $name,
			'email'       => $email,
			'phone'       => $phone,
			'description' => $description,
			'rating'      => $rating,
		] );

		$_testimonial = ( new Testimonial() )->find_by_id( $id );

		wp_send_json_success( $_testimonial, 201 );
	}

	/**
	 * Get request areas
	 */
	public function get_request_areas() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view request areas.', 401 );
		}

		$items  = ( new UnsupportedArea() )->find();
		$counts = ( new UnsupportedArea() )->count_records();

		wp_send_json_success( [ 'items' => $items, 'counts' => $counts ], 200 );
	}

	/**
	 * Subscribe to email
	 */
	public function create_request_areas() {
		$id           = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$email        = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$zip_code     = isset( $_POST['zip_code'] ) ? sanitize_text_field( $_POST['zip_code'] ) : '';
		$device_title = isset( $_POST['device_title'] ) ? sanitize_text_field( $_POST['device_title'] ) : '';
		$device_model = isset( $_POST['device_model'] ) ? sanitize_text_field( $_POST['device_model'] ) : '';
		$device_color = isset( $_POST['device_color'] ) ? sanitize_text_field( $_POST['device_color'] ) : '';

		$area = new UnsupportedArea();
		$data = array(
			'id'           => $id,
			'email'        => $email,
			'zip_code'     => $zip_code,
			'device_title' => $device_title,
			'device_model' => $device_model,
			'device_color' => $device_color,
		);

		if ( ! is_email( $email ) ) {
			$id = $area->create( $data );
			wp_send_json_success( [ 'id' => $id ], 201 );
		} else {
			$area->update( $data );
			wp_send_json_success( [ 'updated' => true ], 201 );
		}

		wp_send_json_error( null, 500 );
	}

	/**
	 * Confirm Appointment
	 *
	 * @throws \WC_Data_Exception
	 * @throws \Exception
	 */
	public function confirm_appointment() {
		$product_id        = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$device_id         = isset( $_POST['device_id'] ) ? sanitize_text_field( $_POST['device_id'] ) : '';
		$device_title      = isset( $_POST['device_title'] ) ? sanitize_text_field( $_POST['device_title'] ) : '';
		$device_color      = isset( $_POST['device_color'] ) ? sanitize_text_field( $_POST['device_color'] ) : '';
		$device_model      = isset( $_POST['device_model'] ) ? sanitize_text_field( $_POST['device_model'] ) : '';
		$issues            = isset( $_POST['issues'] ) && is_array( $_POST['issues'] ) ? $_POST['issues'] : [];
		$issueDescription  = isset( $_POST['issue_description'] ) ? sanitize_text_field( $_POST['issue_description'] ) : '';
		$date              = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : '';
		$time_range        = isset( $_POST['time_range'] ) ? sanitize_text_field( $_POST['time_range'] ) : '';
		$first_name        = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name         = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$phone             = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$email             = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$address           = isset( $_POST['address'] ) && is_array( $_POST['address'] ) ? $_POST['address'] : [];
		$instructions      = isset( $_POST['instructions'] ) ? sanitize_text_field( $_POST['instructions'] ) : '';
		$additionalAddress = isset( $_POST['additional_address'] ) ? sanitize_text_field( $_POST['additional_address'] ) : '';

		// Now we create the order
		$order = new \WC_Order();

		// Add Product
		$product = wc_get_product( $product_id );
		$item_id = $order->add_product( $product, 1, [
			'subtotal' => 0,
			'total'    => 0,
		] );
		wc_update_order_item_meta( $item_id, '_device_id', $device_id );
		wc_update_order_item_meta( $item_id, '_device_title', $device_title );
		wc_update_order_item_meta( $item_id, '_device_model', $device_model );
		wc_update_order_item_meta( $item_id, '_device_color', $device_color );

		$device_issues = [];
		$total_amount  = 0;
		// Add Issue
		foreach ( $issues as $issue ) {
			$item_fee = new \WC_Order_Item_Fee();
			$item_fee->set_name( sanitize_text_field( $issue['title'] ) );
			$item_fee->set_total( floatval( $issue['price'] ) );
			$item_fee->set_total_tax( '0' );
			$item_fee->set_order_id( $order->get_id() );
			$item_fee->save();
			$order->add_item( $item_fee );
			$total_amount += floatval( $issue['price'] );

			$device_issues[] = sanitize_text_field( $issue['title'] );
		}

		// Set billing address
		$order->set_address( [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'company'    => '',
			'address_1'  => $address['street_number']['short_name'] . ' ' . $address['street_address']['long_name'],
			'address_2'  => '',
			'city'       => $address['street_number']['long_name'],
			'state'      => $address['state']['short_name'],
			'postcode'   => $address['postal_code']['short_name'],
			'country'    => $address['country']['short_name'],
			'email'      => $email,
			'phone'      => $phone,
		] );

		$order->set_customer_id( 0 );

		if ( ! empty( $issueDescription ) ) {
			$order->add_order_note( $issueDescription, false, true );
		}

		if ( ! empty( $instructions ) ) {
			$order->add_order_note( $instructions, false, true );
		}

		$order->add_meta_data( '_preferred_service_date', $date );
		$order->add_meta_data( '_preferred_service_time_range', $time_range );
		$order->add_meta_data( '_additional_address', $additionalAddress );

		// Add device data for SMS
		$order->add_meta_data( '_device_id', $device_id );
		$order->add_meta_data( '_device_title', $device_title );
		$order->add_meta_data( '_device_model', $device_model );
		$order->add_meta_data( '_device_color', $device_color );
		$order->add_meta_data( '_device_issues', $device_issues );

		// Add unique id for reschedule
		$order->add_meta_data( '_reschedule_hash', bin2hex( random_bytes( 20 ) ) );

		$order->save_meta_data();

		// Calculate totals and save data
		$order->set_total( $total_amount );
		$order->set_status( 'processing' );
		$order->save();

		do_action( 'stackonet_order_created', $order );

		wp_send_json_success( null, 201 );
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
	 * Get device
	 */
	public function get_device() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view device.', 401 );
		}
		$id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : 0;

		$data = Device::get_device( $id );

		if ( empty( $data ) ) {
			wp_send_json_error( 'Device not found', 404 );
		}

		wp_send_json_success( $data, 200 );
	}

	/**
	 * Create device
	 */
	public function create_device() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to create new service area.', 401 );
		}

		$id = isset( $_POST['id'] ) ? $_POST['id'] : 0;

		$defaults = array(
			'product_id'          => 0,
			'device_title'        => '',
			'device_group'        => '',
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

		if ( ! empty( $id ) ) {
			$data['id'] = $id;
			$response   = Device::update( $data );
		} else {
			$response = Device::create( $data );
		}

		wp_send_json_success( $response, 201 );
	}

	public static function delete_device_issue() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to delete device issue.', 401 );
		}

		$id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : 0;

		if ( DeviceIssue::delete( $id ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	public static function delete_service_area() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to delete service area.', 401 );
		}

		$id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : 0;

		if ( ServiceArea::delete( $id ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Delete device
	 */
	public static function delete_device() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to delete device.', 401 );
		}

		$id = isset( $_POST['id'] ) ? $_POST['id'] : 0;

		if ( Device::delete( $id ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
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

		$id       = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$zip_code = isset( $_POST['zip_code'] ) ? sanitize_text_field( $_POST['zip_code'] ) : '';
		$address  = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';

		if ( empty( $zip_code ) ) {
			wp_send_json_error( 'Zip code is required.' );
		}

		if ( $id ) {
			$data = ServiceArea::update( $id, [
				'zip_code' => $zip_code,
				'address'  => $address,
			] );
		} else {
			$data = ServiceArea::create( [
				'zip_code' => $zip_code,
				'address'  => $address,
			] );
		}

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

		$id    = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$price = isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : '';

		if ( empty( $title ) ) {
			wp_send_json_error( 'Issue title is required.' );
		}

		if ( $id ) {
			$data = DeviceIssue::update( $id, [
				'title' => $title,
				'price' => $price,
			] );
		} else {
			$data = DeviceIssue::create( [
				'title' => $title,
				'price' => $price,
			] );
		}

		wp_send_json_success( $data, $id ? 200 : 201 );
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
