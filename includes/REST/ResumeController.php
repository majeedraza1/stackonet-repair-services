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

class ResumeController extends ApiController {

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
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'technician' ], ],
		] );
	}

	/**
	 * Save become a technician request
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function technician( WP_REST_Request $request ) {
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
