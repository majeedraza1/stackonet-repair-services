<?php

namespace Stackonet\Integrations;

use SquareConnect\Api\TransactionsApi;
use SquareConnect\ApiClient;
use SquareConnect\ApiException;
use SquareConnect\Configuration;
use SquareConnect\Model\ChargeRequest;
use SquareConnect\Model\ChargeResponse;
use Stackonet\Models\Settings;
use WC_Order;

class Square {

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
	 * @var ApiClient
	 */
	private $api_client;

	/**
	 * Square constructor.
	 *
	 * @param string $applicationID
	 * @param string $accessToken
	 * @param string $locationId
	 */
	public function __construct( $applicationID = '', $accessToken = '', $locationId = '' ) {
		$this->applicationID = ! empty( $applicationID ) ? $applicationID : Settings::get_square_payment_application_id();
		$this->accessToken   = ! empty( $accessToken ) ? $accessToken : Settings::get_square_payment_access_token();
		$this->locationId    = ! empty( $locationId ) ? $locationId : Settings::get_square_payment_location_id();

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
	 * @param WC_Order $order
	 *
	 * @return ChargeResponse|ApiException
	 */
	public function charge_card_nonce( $nonce, WC_Order $order ) {
		# create an instance of the Transaction API class
		$transactions_api = new TransactionsApi( $this->api_client );

		$address = [
			'first_name'                      => $order->get_billing_first_name(),
			'last_name'                       => $order->get_billing_last_name(),
			'organization'                    => $order->get_billing_company(),
			'address_line_1'                  => $order->get_billing_address_1(),
			'address_line_2'                  => $order->get_billing_address_2(),
			'locality'                        => $order->get_billing_city(),
			'administrative_district_level_1' => $order->get_billing_state(),
			'postal_code'                     => $order->get_billing_postcode(),
			'country'                         => $order->get_billing_country(),
		];

		$body = new ChargeRequest( [
			"card_nonce"          => $nonce,
			"amount_money"        => [
				"amount"   => self::format_amount_to_square( $order->get_total() ),
				"currency" => get_woocommerce_currency()
			],
			"idempotency_key"     => uniqid(),
			"billing_address"     => $address,
			"shipping_address"    => $address,
			"buyer_email_address" => $order->get_billing_email(),
		] );

		try {
			$result = $transactions_api->charge( $this->locationId, $body );
		} catch ( ApiException $e ) {
			$result = $e;
		}

		return $result;
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

	/**
	 * Process amount to be passed from Square.
	 *
	 * @param int $total
	 * @param string $currency
	 *
	 * @return float
	 */
	public static function format_amount_from_square( $total, $currency = '' ) {
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
				$total = wc_format_decimal( absint( $total ) / 100 );
				break;
		}

		return $total;
	}
}
