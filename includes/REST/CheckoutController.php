<?php

namespace Stackonet\REST;

use DateTime;
use Exception;
use SquareConnect\Model\ChargeResponse;
use SquareConnect\Model\Tender;
use SquareConnect\Model\Transaction;
use Stackonet\Integrations\Square;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class CheckoutController extends ApiController {
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
		register_rest_route( $this->namespace, '/checkout', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'checkout' ] ],
		] );
	}

	/**
	 * Get one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function checkout( $request ) {
		$order_id = (int) $request->get_param( 'order' );
		$nonce    = $request->get_param( 'nonce' );

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound( null, 'No order found.' );
		}

		if ( ! $order->needs_payment() ) {
			return $this->respondUnprocessableEntity( null, 'Payment already complete.' );
		}

		$square = new Square();
		$result = $square->charge_card_nonce( $nonce, $order );
		if ( $result instanceof ChargeResponse ) {
			$this->update_order_payment_data( $order, $result->getTransaction() );

			return $this->respondOK( null, '' );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * @param WC_Order $order
	 * @param Transaction $transaction
	 *
	 * @throws Exception
	 */
	private function update_order_payment_data( WC_Order $order, Transaction $transaction ) {
		$tenders = $transaction->getTenders();

		/** @var Tender $tender */
		$tender       = isset( $tenders[0] ) ? $tenders[0] : null;
		$card_details = $tender->getCardDetails();
		$card         = $card_details->getCard();
		$isCaptured   = ( $card_details->getStatus() == 'CAPTURED' ) ? 'yes' : 'no';
		$created_at   = $transaction->getCreatedAt();
		$date         = new DateTime( $created_at );
		$money        = $tender->getAmountMoney();
		$amount       = Square::format_amount_from_square( $money->getAmount(), $money->getCurrency() );

		$order->add_meta_data( '_square_credit_card_trans_id', $transaction->getId() );
		$order->add_meta_data( '_square_credit_card_trans_date', $date->format( 'Y-m-d H:i:s' ) );
		$order->add_meta_data( '_square_credit_card_card_type', $card->getCardBrand() );
		$order->add_meta_data( '_square_credit_card_card_last_four', $card->getLast4() );
		$order->add_meta_data( '_square_credit_card_charge_captured', $isCaptured );
		$order->add_meta_data( '_square_credit_card_authorization_code', $card->getFingerprint() );
		$order->add_meta_data( '_square_credit_card_authorization_amount', $amount );
		// $order->add_meta_data( '_paid_date', $date->format( 'Y-m-d H:i:s' ) );

		$order->save_meta_data();

		$order->payment_complete( $transaction->getId() );
	}
}
