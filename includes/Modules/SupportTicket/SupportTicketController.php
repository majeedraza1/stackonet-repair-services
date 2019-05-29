<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\REST\ApiController;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class SupportTicketController extends ApiController {

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
		register_rest_route( $this->namespace, '/support-ticket', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ], ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_item' ] ],
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/agent', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_agent' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/thread', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_thread' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/thread/(?P<thread_id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_thread' ] ],
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_thread' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/batch_delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_items' ] ],
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
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$status          = $request->get_param( 'ticket_status' );
		$ticket_category = $request->get_param( 'ticket_category' );
		$ticket_priority = $request->get_param( 'ticket_priority' );
		$per_page        = $request->get_param( 'per_page' );
		$paged           = $request->get_param( 'paged' );

		$status          = ! empty( $status ) ? $status : 'all';
		$ticket_category = ! empty( $ticket_category ) ? $ticket_category : 'all';
		$ticket_priority = ! empty( $ticket_priority ) ? $ticket_priority : 'all';
		$per_page        = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged           = ! empty( $paged ) ? absint( $paged ) : 1;

		$supportTicket = new SupportTicket;

		$items  = $supportTicket->find( [
			'paged'           => $paged,
			'per_page'        => $per_page,
			'ticket_status'   => $status,
			'ticket_category' => $ticket_category,
			'ticket_priority' => $ticket_priority,
		] );
		$counts = $supportTicket->count_records();

		$pagination = $supportTicket->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );;

		$response = [ 'items' => $items, 'counts' => $counts, 'pagination' => $pagination ];

		return $this->respondOK( $response );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function get_item( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$id = (int) $request->get_param( 'id' );

		$supportTicket = new SupportTicket;
		$item          = $supportTicket->find_by_id( $id );
		$data          = $item->to_array();
		$threads       = $item->get_ticket_threads();

		return $this->respondOK( [ 'ticket' => $data, 'threads' => $threads ] );
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return $this->respondUnauthorized();
		}

		$id = (int) $request->get_param( 'id' );

		$supportTicket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		$data = $request->get_params();

		if ( ( new SupportTicket() )->update( $data ) ) {
			return $this->respondOK();
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
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

		$class  = new SupportTicket();
		$survey = $class->find_by_id( $id );

		if ( ! $survey instanceof SupportTicket ) {
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

		return $this->respondOK( "#{$id} Support ticket has been deleted" );
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
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

		$class = new SupportTicket();

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

		return $this->respondOK( "Support tickets has been deleted" );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_thread( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$id             = (int) $request->get_param( 'id' );
		$thread_type    = $request->get_param( 'thread_type' );
		$thread_content = $request->get_param( 'thread_content' );

		if ( empty( $id ) || empty( $thread_type ) || empty( $thread_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket ID, thread type and thread content is required.' );
		}

		if ( ! in_array( $thread_type, [ 'note', 'reply' ] ) ) {
			return $this->respondUnprocessableEntity( null, 'Only note and reply are supported.' );
		}

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		$user = wp_get_current_user();

		$support_ticket->add_ticket_info( $id, [
			'thread_type'    => $thread_type,
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $thread_content,
			'agent_created'  => $user->ID,
		] );

		return $this->respondCreated();
	}

	/**
	 * Update thread content
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_thread( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$id           = (int) $request->get_param( 'id' );
		$thread_id    = (int) $request->get_param( 'thread_id' );
		$post_content = $request->get_param( 'post_content' );

		if ( empty( $id ) || empty( $thread_id ) || empty( $post_content ) ) {
			return $this->respondUnprocessableEntity();
		}

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		$thread = get_post( $thread_id );

		if ( ! $thread instanceof WP_Post ) {
			return $this->respondNotFound( null, 'Sorry, no thread found.' );
		}

		$response = wp_update_post( [
			'ID'           => $thread_id,
			'post_content' => $post_content,
		] );

		if ( ! $response instanceof WP_Error ) {
			return $this->respondOK( $post_content );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_agent( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$id    = (int) $request->get_param( 'id' );
		$agent = $request->get_param( 'agents_ids' );

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		$support_ticket->update_agent( $agent );

		return $this->respondOK();
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_thread( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$id        = (int) $request->get_param( 'id' );
		$thread_id = (int) $request->get_param( 'thread_id' );

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( $support_ticket->delete_thread( $thread_id ) ) {
			return $this->respondOK( [ $id, $thread_id ] );
		}

		return $this->respondInternalServerError();
	}
}
