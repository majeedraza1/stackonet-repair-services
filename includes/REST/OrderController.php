<?php

namespace Stackonet\REST;

use Exception;
use Stackonet\Emails\PaymentLinkCustomerEmail;
use Stackonet\Integrations\Twilio;
use Stackonet\Models\Settings;
use Stackonet\Modules\SupportTicket\SupportTicket;
use Stackonet\Supports\Utils;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

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
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'add_discount' ] ],
		] );
		register_rest_route( $this->namespace, '/order/(?P<id>\d+)/custom-payment-amount', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'add_custom_amount' ] ],
		] );
		register_rest_route( $this->namespace, '/order/(?P<id>\d+)/custom-payment-sms', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_custom_payment_sms' ] ],
		] );
		register_rest_route( $this->namespace, '/order/(?P<id>\d+)/sms', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_sms' ] ],
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

		$payment_url     = Utils::shorten_url( $payment_url );
		$save_order_meta = false;

		if ( in_array( $media, [ 'email', 'both' ] ) ) {
			$email = wc()->mailer()->get_emails();
			/** @var PaymentLinkCustomerEmail $user_email */
			$user_email = $email['customer_order_payment_link'];
			$user_email->trigger( $order->get_id(), $order, $payment_url );
			$supportTicket->add_note( $ticket_id, 'Payment link email has been sent to customer.', 'email' );
			$order->update_meta_data( '_payment_link_email_sent', 'yes' );
			$save_order_meta = true;
		}

		if ( in_array( $media, [ 'sms', 'both' ] ) ) {
			$sms_content = sprintf(
				"Hi %s, Please click the link to make a payment for the order #%s ",
				$order->get_formatted_billing_full_name(),
				$order->get_id()
			);
			$sms_content .= $payment_url;
			$supportTicket->add_note( $ticket_id, $sms_content, 'sms' );

			$phones = [ $order->get_billing_phone() ];
			( new Twilio() )->send_support_ticket_sms( $phones, $sms_content );
			$order->update_meta_data( '_payment_link_sms_sent', 'yes' );
			$save_order_meta = true;
		}

		if ( $save_order_meta ) {
			$order->save_meta_data();
		}

		return $this->respondOK();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function send_custom_payment_sms( $request ) {
		$order_id  = (int) $request->get_param( 'id' );
		$ticket_id = $request->get_param( 'ticket_id' );
		$amount    = $request->get_param( 'amount' );
		$hash      = $request->get_param( 'hash' );
		$media     = $request->get_param( 'media' );

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
			'_type' => 'custom',
			'_hash' => $hash,
		], $page_url );

		$payment_url = Utils::shorten_url( $payment_url );

		if ( in_array( $media, [ 'email', 'both' ] ) ) {
			$email = wc()->mailer()->get_emails();
			/** @var PaymentLinkCustomerEmail $user_email */
			$user_email = $email['customer_custom_payment_link_email'];
			$user_email->trigger( $order->get_id(), $order, $payment_url );
			$supportTicket->add_note( $ticket_id, 'Custom Payment link email has been sent to customer.', 'email' );
		}

		if ( in_array( $media, [ 'sms', 'both' ] ) ) {
			$sms_content = sprintf(
				"Hi %s, Please click the link to make a payment %s",
				$order->get_formatted_billing_full_name(),
				$payment_url
			);
			$supportTicket->add_note( $ticket_id, $sms_content, 'sms' );

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

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 * @throws Exception
	 */
	public function add_custom_amount( $request ) {
		$order_id  = (int) $request->get_param( 'id' );
		$ticket_id = (int) $request->get_param( 'ticket_id' );
		$amount    = (float) $request->get_param( 'amount' );

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound( null, 'No order found with this id.' );
		}

		$ticket = ( new SupportTicket() )->find_by_id( $ticket_id );

		if ( ! $ticket instanceof SupportTicket ) {
			return $this->respondNotFound( null, 'No support ticket found with this id.' );
		}

		$data = [
			'support_id' => $ticket_id,
			'order_id'   => $order_id,
			'amount'     => $amount,
			'hash'       => bin2hex( random_bytes( 20 ) ),
		];

		$custom_amount   = get_post_meta( $order_id, '_custom_payment_amount', true );
		$custom_amount   = is_array( $custom_amount ) ? $custom_amount : [];
		$custom_amount[] = $data;

		update_post_meta( $order_id, '_custom_payment_amount', $custom_amount );

		return $this->respondOK( $request->get_params() );
	}
}
