<?php

namespace Stackonet\REST;

use Stackonet\Integrations\Twilio;
use Stackonet\Models\Settings;
use Stackonet\Modules\SupportTicket\SupportTicket;
use Stackonet\Supports\Utils;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class OrderController extends ApiController {

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
		register_rest_route( $this->namespace, '/order/(?P<id>\d+)/discount', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'add_discount' ], ],
		] );
		register_rest_route( $this->namespace, '/order/(?P<id>\d+)/sms', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_sms' ], ],
		] );
	}

	/**
	 * Add discount to order
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function send_sms( $request ) {
		$order_id  = (int) $request->get_param( 'id' );
		$media     = $request->get_param( 'media' );
		$ticket_id = $request->get_param( 'ticket_id' );

		if ( ! in_array( $media, [ 'sms', 'email', 'both' ] ) ) {
			return $this->respondUnprocessableEntity( null, 'Unsupported media type' );
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound( null, 'No order found!' );
		}

		$supportTicket = ( new SupportTicket )->find_by_id( $ticket_id );
		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound( null, 'No support ticket found!' );
		}

		$payment_page_id = Settings::get_payment_page_id();
		$page_url        = get_permalink( $payment_page_id );
		$payment_url     = add_query_arg( [
			'order' => $order->get_id(),
			'token' => $order->get_meta( '_reschedule_hash', true ),
		], $page_url );

		if ( ! in_array( $media, [ 'email', 'both' ] ) ) {
			$email_content = $payment_url;
		}

		if ( ! in_array( $media, [ 'sms', 'both' ] ) ) {
			$sms_content = $payment_url;
			$user        = wp_get_current_user();
			$supportTicket->add_ticket_info( $ticket_id, [
				'thread_type'    => 'sms',
				'customer_name'  => $user->display_name,
				'customer_email' => $user->user_email,
				'post_content'   => $sms_content,
				'agent_created'  => $user->ID,
			] );

			$phones = [ $order->get_billing_phone() ];
			( new Twilio() )->send_support_ticket_sms( $phones, $sms_content );
		}

		return $this->respondOK();
	}

	/**
	 * Add discount to order
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function add_discount( $request ) {
		$order_id      = (int) $request->get_param( 'id' );
		$amount        = (float) $request->get_param( 'amount' );
		$discount_type = $request->get_param( 'discount_type' );
		$ticket_id     = $request->get_param( 'ticket_id' );

		if ( ! in_array( $discount_type, [ 'fixed', 'percentage' ] ) ) {
			return $this->respondUnprocessableEntity( null, 'Unsupported discount type' );
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound();
		}

		$title           = ( $discount_type == 'percentage' ) ? 'Extra Discount (%)' : 'Extra Discount (Amount)';
		$amount          = ( $discount_type == 'percentage' ) ? $amount . '%' : $amount;
		$discount_amount = Utils::add_order_discount( $order_id, $title, $amount );

		$user              = wp_get_current_user();
		$discounted_amount = wc_price( $discount_amount );

		$note = "<strong>{$user->display_name}</strong> added <strong>{$discounted_amount}</strong> as <strong>{$title}</strong>.";

		$order->add_order_note( $note, true );

		$supportTicket = ( new SupportTicket )->find_by_id( $ticket_id );
		if ( $supportTicket instanceof SupportTicket ) {
			$order        = wc_get_order( $order_id );
			$total_amount = $order->get_formatted_order_total();
			$note         .= " New order total amount is <strong>{$total_amount}</strong>";
			$supportTicket->add_ticket_info( $ticket_id, [
				'thread_type'    => 'note',
				'customer_name'  => $user->display_name,
				'customer_email' => $user->user_email,
				'post_content'   => $note,
				'agent_created'  => $user->ID,
			] );
		}

		return $this->respondOK();
	}
}
