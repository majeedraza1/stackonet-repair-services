<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Supports\Attachment;
use Stackonet\Supports\UploadedFile;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class LogoController extends ApiController {

	/**
	 * @var self
	 */
	private static $instance;

	/**
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
		register_rest_route( $this->namespace, '/logo', array(
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'upload_logo' ),
			),
		) );
		register_rest_route( $this->namespace, '/logo/(?P<id>\d+)', array(
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_logo' ),
			),
		) );
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

		$image_id       = $ids[0];
		$attachment_url = wp_get_attachment_url( $image_id );
		$image          = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$full_image     = wp_get_attachment_image_src( $image_id, 'full' );
		$title          = get_the_title( $image_id );

		$token = wp_generate_password( 20, false, false );
		update_post_meta( $image_id, '_delete_token', $token );

		$response = [
			'image_id'       => $image_id,
			'token'          => $token,
			'title'          => $title,
			'attachment_url' => $attachment_url,
			'thumbnail'      => [ 'src' => $image[0], 'width' => $image[1], 'height' => $image[2], ],
			'full'           => [ 'src' => $full_image[0], 'width' => $full_image[1], 'height' => $full_image[2], ],
		];

		return $this->respondOK( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_logo( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$token = $request->get_param( 'token' );

		if ( self::can_delete_logo( $id, $token ) ) {
			return $this->respondUnauthorized();
		}

		$_post = get_post( $id );

		if ( ! $_post instanceof \WP_Post ) {
			return $this->respondNotFound( null, 'Image not found!' );
		}

		wp_delete_post( $id, true );

		return $this->respondOK( null, [ 'deleted' => true ] );
	}

	/**
	 * If current user can delete media
	 *
	 * @param int $post_id
	 * @param string $token
	 *
	 * @return bool
	 */
	private static function can_delete_logo( $post_id, $token = '' ) {
		$delete_token = get_post_meta( $post_id, '_delete_token', true );

		return current_user_can( 'manage_options' ) || ( $token == $delete_token );
	}
}
