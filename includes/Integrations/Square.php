<?php

namespace Stackonet\Integrations;

use SquareConnect\Api\CheckoutApi;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\ApiClient;
use SquareConnect\ApiException;
use SquareConnect\Configuration;
use SquareConnect\Model\ChargeRequest;
use SquareConnect\Model\ChargeResponse;
use SquareConnect\Model\CreateCheckoutRequest;
use SquareConnect\Model\CreateOrderRequest;
use SquareConnect\Model\CreateOrderRequestLineItem;
use SquareConnect\Model\Money;
use Stackonet\Supports\Logger;

class Square {

	/**
	 * Application Name
	 *
	 * @var string
	 */
	protected $applicationName;

	/**
	 * Application ID
	 *
	 * @var string
	 */
	protected $applicationID;

	/**
	 * Access Token
	 *
	 * @var string
	 */
	protected $accessToken;

	/**
	 * Location Id
	 *
	 * @var string
	 */
	protected $locationId;

	/**
	 * @var CheckoutApi
	 */
	private $checkoutClient;
	/**
	 * @var ApiClient
	 */
	private $api_client;

	/**
	 * Square constructor.
	 *
	 * @param string $accessToken
	 * @param string $locationId
	 */
	public function __construct( $accessToken = '', $locationId = '' ) {
		$this->accessToken = $accessToken;
		$this->locationId  = $locationId;

		$this->applicationName = 'Phone Repairs ASAP';
		$this->applicationID   = 'sandbox-sq0idp-ZWCqBQE_su61oWsKiBi5cw';
		$this->accessToken     = 'EAAAELinI3CXmA_Ln5SSAMoTC4hCalEMk2fOgrENhSyXrX5OGPENGlXy1P9uAz_r';
		$this->locationId      = 'CBASEF3_KWP_i8_DGzvjsrSYmwsgAQ';

		$this->initialize_api_client();
	}

	/**
	 * Initialize an API client
	 */
	public function initialize_api_client() {
		// Create and configure a new API client object
		$defaultApiConfig = new Configuration();
		$defaultApiConfig->setAccessToken( $this->accessToken );
		$this->api_client = new ApiClient( $defaultApiConfig );
	}

	/**
	 * @param string $nonce
	 * @param float $amount
	 * @param string $currency
	 *
	 * @return ChargeResponse|ApiException
	 */
	public function charge_card_nonce( $nonce, $amount, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		# create an instance of the Transaction API class
		$transactions_api = new TransactionsApi( $this->api_client );

		$body = new ChargeRequest( [
			"card_nonce"      => $nonce,
			"amount_money"    => [
				"amount"   => self::format_amount_to_square( $amount ),
				"currency" => $currency
			],
			"idempotency_key" => uniqid()
		] );

		try {
			$result = $transactions_api->charge( $this->locationId, $body );
		} catch ( ApiException $e ) {
			$result = $e;
		}

		return $result;
	}

	/**
	 * Create an order for your checkout request
	 */
	public function create_order() {
		//Create a Money object to represent the price of the line item.
		$price = new Money;
		$price->setAmount( 600 );
		$price->setCurrency( 'USD' );

		//Create the line item and set details
		$book = new CreateOrderRequestLineItem;
		$book->setName( 'The Shining' );
		$book->setQuantity( '2' );
		$book->setBasePriceMoney( $price );

		//Puts our line item object in an array called lineItems.
		$lineItems = array();
		array_push( $lineItems, $book );

		// Create an Order object using line items from above
		$order = new CreateOrderRequest();

		$order->setIdempotencyKey( uniqid() ); //uniqid() generates a random string.

		//sets the lineItems array in the order object
		$order->setLineItems( $lineItems );

		return $order;
	}

	/**
	 * creates a CreateCheckout request object
	 *
	 * @param CreateOrderRequest $order
	 * @param string $redirect_url
	 *
	 * @return CreateCheckoutRequest
	 */
	public function create_checkout_request_object( CreateOrderRequest $order, $redirect_url = '' ) {
		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url();
		}

		// Create Checkout request object.
		$checkout = new CreateCheckoutRequest();

		$checkout->setIdempotencyKey( uniqid() ); //uniqid() generates a random string.
		$checkout->setOrder( $order ); //this is the order we created in the previous step.
		$checkout->setRedirectUrl( $redirect_url ); //Replace with the URL where you want to redirect your customers after transaction.

		return $checkout;
	}

	/**
	 * Send the itemized order to the Square Checkout endpoint
	 *
	 * @param CreateCheckoutRequest $checkout
	 *
	 * @return string
	 */
	public function getCheckoutPageUrl( CreateCheckoutRequest $checkout ) {
		$checkoutUrl = '';

		try {
			$result = $this->checkoutClient->createCheckout(
				$this->locationId,
				$checkout
			);
			//Save the checkout ID for verifying transactions
			$checkoutId = $result->getCheckout()->getId();
			//Get the checkout URL that opens the checkout page.
			$checkoutUrl = $result->getCheckout()->getCheckoutPageUrl();
		} catch ( ApiException $e ) {
			var_dump( $e );
			die();
			Logger::log( $e->getMessage() );
		}

		return $checkoutUrl;
	}

	/**
	 * Process amount to be passed to Square.
	 *
	 * @param int|float $total
	 * @param string $currency
	 *
	 * @return float
	 */
	public static function format_amount_to_square( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		switch ( strtoupper( $currency ) ) {
			// Zero decimal currencies
			case 'BIF':
			case 'CLP':
			case 'DJF':
			case 'GNF':
			case 'JPY':
			case 'KMF':
			case 'KRW':
			case 'MGA':
			case 'PYG':
			case 'RWF':
			case 'VND':
			case 'VUV':
			case 'XAF':
			case 'XOF':
			case 'XPF':
				$total = absint( $total );
				break;
			default:
				$total = absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
				break;
		}

		return $total;
	}
}
