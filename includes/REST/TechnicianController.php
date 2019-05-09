<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Models\Technician;
use Stackonet\Supports\Attachment;
use Stackonet\Supports\DropboxHelper;
use Stackonet\Supports\UploadedFile;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TechnicianController extends ApiController {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Initiate plugin
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/resume', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'upload_logo' ], ],
		] );
		register_rest_route( $this->namespace, '/technician', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ], ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ], ],
		] );
		register_rest_route( $this->namespace, '/technician/delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/technician/batch_delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_items' ] ],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$status   = $request->get_param( 'status' );
		$per_page = $request->get_param( 'per_page' );
		$paged    = $request->get_param( 'paged' );

		$status   = ! empty( $status ) ? $status : 'all';
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged    = ! empty( $paged ) ? absint( $paged ) : 1;

		$technician = new Technician();

		$items      = $technician->find( [ 'status' => $status, 'paged' => $paged, 'per_page' => $per_page, ] );
		$counts     = $technician->count_records();
		$pagination = $technician->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );;

		$response = [ 'items' => $items, 'counts' => $counts, 'pagination' => $pagination ];

		return $this->respondOK( $response );
	}

	/**
	 * Save become a technician request
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function create_item( $request ) {
		$first_name = $request->get_param( 'first_name' );
		$last_name  = $request->get_param( 'last_name' );
		$email      = $request->get_param( 'email' );
		$phone      = $request->get_param( 'phone' );
		$resume_id  = $request->get_param( 'resume_id' );

		$technician = new Technician();
		$id         = $technician->create( [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'phone'      => $phone,
			'resume_id'  => $resume_id,
		] );

		if ( ! $id ) {
			return $this->respondInternalServerError();
		}

		return $this->respondCreated();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
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

		$class  = new Technician();
		$survey = $class->find_by_id( $id );

		if ( ! $survey instanceof Technician ) {
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
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
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

		$class = new Technician();

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

	/**
	 * Upload a file to a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object.
	 * @throws Exception
	 */
	public function upload_logo( WP_REST_Request $request ) {
		$files = UploadedFile::getUploadedFiles();

		if ( ! isset( $files['file'] ) ) {
			return $this->respondForbidden();
		}

		if ( ! $files['file'] instanceof UploadedFile ) {
			return $this->respondForbidden();
		}

		$attachments = Attachment::upload( $files['file'] );
		$ids         = wp_list_pluck( $attachments, 'attachment_id' );
		$files_paths = wp_list_pluck( $attachments, 'attachment_path' );

		foreach ( $files_paths as $files_path ) {
			( new DropboxHelper() )->upload( $files_path, '/resume/' );
		}

		$image_id = $ids[0];

		$token = wp_generate_password( 20, false, false );
		update_post_meta( $image_id, '_delete_token', $token );

		$response = $this->prepare_item_for_response( $image_id, $request );

		return $this->respondOK( $response );
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $item, $request ) {
		$image_id       = $item;
		$title          = get_the_title( $image_id );
		$token          = get_post_meta( $image_id, '_delete_token', true );
		$attachment_url = wp_get_attachment_url( $image_id );
		$image          = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$full_image     = wp_get_attachment_image_src( $image_id, 'full' );

		$response = [
			'image_id'       => $image_id,
			'title'          => $title,
			'token'          => $token,
			'attachment_url' => $attachment_url,
			'thumbnail'      => [ 'src' => $image[0], 'width' => $image[1], 'height' => $image[2], ],
			'full'           => [ 'src' => $full_image[0], 'width' => $full_image[1], 'height' => $full_image[2], ],
		];

		return $response;
	}
}
