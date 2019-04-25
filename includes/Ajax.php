<?php

namespace Stackonet;

use Exception;
use Stackonet\Models\Device;
use Stackonet\Models\DeviceIssue;
use Stackonet\Models\Phone;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;
use Stackonet\Models\Testimonial;
use Stackonet\Models\UnsupportedArea;
use Stackonet\Supports\Utils;
use WC_Data_Exception;
use WC_Order;
use WC_Order_Item_Fee;
use WP_Error;

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

			// Manager Registration
			add_action( 'wp_ajax_nopriv_manager_registration', [ self::$instance, 'manager_registration' ] );

			add_action( 'wp_ajax_stackonet_test', [ self::$instance, 'stackonet_test' ] );

			// Phones
			add_action( 'wp_ajax_get_phones', [ self::$instance, 'get_phones' ] );
			add_action( 'wp_ajax_delete_phone', [ self::$instance, 'delete_phone' ] );
			add_action( 'wp_ajax_batch_delete_phones', [ self::$instance, 'delete_phones' ] );
			add_action( 'wp_ajax_add_phone_note', [ self::$instance, 'add_phone_note' ] );
			add_action( 'wp_ajax_download_phones_csv', [ self::$instance, 'download_phones_csv' ] );
		}

		return self::$instance;
	}

	public function stackonet_test() {
		var_dump( 'working' );
		die();
	}

	/**
	 * Download Phone CSV
	 */
	public function download_phones_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to download phones CSV file.', 401 );
		}

		$status        = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all';
		$per_page      = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 20;
		$paged         = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
		$search        = isset( $_REQUEST['search'] ) ? $_REQUEST['search'] : null;
		$store_address = isset( $_REQUEST['store_address'] ) ? $_REQUEST['store_address'] : null;

		$phone = new Phone();

		if ( ! empty( $store_address ) ) {
			$phones = $phone->search_store_address( [ 'store_address' => $store_address, 'status' => $status ] );
		} else if ( ! empty( $search ) ) {
			$phones = $phone->search( [ 'search' => $search, 'status' => $status ] );
		} else {
			$phones = $phone->find( [ 'per_page' => $per_page, 'paged' => $paged, 'status' => $status ] );
		}

		$filename = sprintf( 'phones-status-%s-page-%s.csv', $status, $paged );

		$header = [
			'Asset Number',
			'Brand Name',
			'Model',
			'Color',
			'IMEI Number',
			'Broken Screen',
			'issues',
			'Status',
			'Created By',
			'Store Address'
		];

		$rows = [ $header ];

		/** @var Phone[] $phones */
		foreach ( $phones as $_phone ) {
			$issues = $_phone->get( 'issues' );
			$rows[] = [
				$_phone->get( 'id' ),
				$_phone->get( 'brand_name' ),
				$_phone->get( 'model' ),
				$_phone->get( 'color' ),
				$_phone->get( 'imei_number' ),
				$_phone->get( 'broken_screen' ),
				is_array( $issues ) ? implode( ", ", $issues ) : '',
				$_phone->get( 'status' ),
				$_phone->get_author()->display_name,
				$_phone->get_author_store_address(),
			];
		}

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/csv; charset=UTF-8' );
		@header( 'Content-Disposition: filename="' . $filename . '"' );
		@header( 'Expires: 0' );
		@header( 'Cache-Control: must-revalidate' );
		@header( 'Pragma: public' );
		echo Utils::generateCsv( $rows );
		die();
	}


	/**
	 * Get phones
	 */
	public function get_phones() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to view phones.', 401 );
		}

		$status        = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all';
		$per_page      = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 20;
		$paged         = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
		$search        = isset( $_REQUEST['search'] ) ? $_REQUEST['search'] : null;
		$store_address = isset( $_REQUEST['store_address'] ) ? $_REQUEST['store_address'] : null;

		$phone = new Phone();

		if ( ! empty( $store_address ) ) {
			$phones = $phone->search_store_address( [ 'store_address' => $store_address, 'status' => $status ] );
		} else if ( ! empty( $search ) ) {
			$phones = $phone->search( [ 'search' => $search, 'status' => $status ] );
		} else {
			$phones = $phone->find( [ 'per_page' => $per_page, 'paged' => $paged, 'status' => $status ] );
		}

		$counts = $phone->count_records();

		$pagination = $phone->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		$response = [ 'items' => $phones, 'counts' => $counts, 'pagination' => $pagination ];
		wp_send_json_success( $response, 200 );
	}

	/**
	 * Add phone note
	 */
	public function add_phone_note() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You have no permission to add phone note.', 401 );
		}

		$phone_id = isset( $_POST['phone_id'] ) ? intval( $_POST['phone_id'] ) : 0;
		$note     = isset( $_POST['note'] ) ? sanitize_textarea_field( $_POST['note'] ) : null;

		if ( empty( $phone_id ) || empty( $note ) ) {
			wp_send_json_error( 'Phone id and note both are required.', 422 );
		}

		$class = new Phone();
		$phone = $class->find_by_id( $phone_id );

		if ( ! $phone instanceof Phone ) {
			wp_send_json_error( 'Phone not found.', 404 );
		}

		$notes = $class->add_note( $phone, $note );

		wp_send_json_success( $notes, 201 );
	}

	/**
	 * Delete company by company id
	 */
	public static function delete_phone() {
		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$action = isset( $_POST['_action'] ) ? sanitize_text_field( $_POST['_action'] ) : '';
		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			wp_send_json_error( 'Unsupported action.', 422 );
		}
		$class   = new Phone();
		$catalog = $class->find_by_id( $id );

		if ( false === $catalog ) {
			wp_send_json_error( 'Phone not found.', 404 );
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
		wp_send_json_success( "#{$id} Phone has been deleted", 200 );
	}

	/**
	 * Delete multiple companies
	 */
	public static function delete_phones() {
		$action = isset( $_POST['_action'] ) ? sanitize_text_field( $_POST['_action'] ) : '';
		$ids    = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? $_POST['ids'] : [];
		$ids    = array_map( 'intval', $ids );
		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			wp_send_json_error( 'Unsupported action.', 422 );
		}

		$class = new Phone();

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
		wp_send_json_success( "Phones has been deleted", 200 );
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
	 * Process manager registration
	 */
	public function manager_registration() {
		$first_name    = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name     = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$phone         = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$email         = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$password      = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
		$store_address = isset( $_POST['store_address'] ) ? sanitize_textarea_field( $_POST['store_address'] ) : '';

		$error = new WP_Error();

		if ( empty( $last_name ) ) {
			$error->add( 'last_name', 'Last name is required.' );
		}

		if ( empty( $phone ) ) {
			$error->add( 'phone', 'Phone number is required.' );
		}

		if ( ! is_email( $email ) ) {
			$error->add( 'email', 'Enter a valid email address.' );
		}

		if ( username_exists( $email ) || email_exists( $email ) ) {
			$error->add( 'email', 'A user account already exists with this email address.' );
		}

		if ( mb_strlen( $password ) < 8 ) {
			$error->add( 'password', 'Password must be at least 8 characters.' );
		}

		if ( mb_strlen( $store_address ) < 3 ) {
			$error->add( 'store_address', 'Store address is required.' );
		}

		if ( ! empty( $error->errors ) ) {
			wp_send_json_error( $error->errors, 422 );
		}

		$user_data = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_email' => $email,
			'user_login' => $email,
			'user_pass'  => $password,
			'role'       => 'manager',
		);

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( 'Fail to create new user.', 500 );
		}

		add_user_meta( $user_id, '_store_address', $store_address );
		add_user_meta( $user_id, '_phone_number', $phone );

		wp_send_json_success( 'New user has been created successfully.', 200 );
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

		$error = new WP_Error();

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
	 * @throws WC_Data_Exception
	 * @throws Exception
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
		$order = new WC_Order();

		$_address = [
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
		];

		// Set billing address
		$order->set_address( $_address, 'billing' );

		// Set shipping address
		$order->set_address( $_address, 'shipping' );

		$order->set_customer_id( 0 );

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
			$item_price = floatval( $issue['price'] );
			$item_tax   = $item_price * 0.07;
			$item_fee   = new WC_Order_Item_Fee();
			$item_fee->set_name( sanitize_text_field( $issue['title'] ) );
			$item_fee->set_total( $item_price );
			$item_fee->set_total_tax( $item_tax );
			$item_fee->set_order_id( $order->get_id() );
			$item_fee->save();
			$order->add_item( $item_fee );
			$total_amount += floatval( $issue['price'] );

			$device_issues[] = sanitize_text_field( $issue['title'] );
		}

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
		$order->set_status( 'on-hold' );
		$order->calculate_taxes();
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
