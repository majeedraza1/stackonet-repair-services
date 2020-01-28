<?php

namespace Stackonet\Integrations;

use Stackonet\Interfaces\IpServiceProviderInterface;

class IpData implements IpServiceProviderInterface {

	/**
	 * @var string
	 */
	const BASE_URL = 'https://api.ipdata.co/';

	/**
	 * IP Address
	 *
	 * @var string
	 */
	protected $ip_address = '127.0.0.1';

	/**
	 * API key
	 *
	 * @var string
	 */
	protected $api_key;

	public function __construct( $api_key ) {
		$this->api_key = $api_key;

		//https://api.ipdata.co/122.172.182.80?api-key=6a75e046bff9d02376babeebf0129acd07457f2e5c9ae4d0cfa0d903
	}

	/**
	 * @return string
	 */
	public function get_ip_address() {
		return $this->ip_address;
	}

	/**
	 * @param string $ip_address
	 */
	public function set_ip_address( $ip_address ) {
		$this->ip_address = $ip_address;
	}

	/**
	 * @return string
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * @param string $api_key
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Build API URL
	 *
	 * @return string
	 */
	public function build_url() {
		return self::BASE_URL . $this->get_ip_address() . '?api-key=' . $this->get_api_key();
	}

	public function get_data() {
		$request = wp_safe_remote_get( $this->build_url() );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );
		$json = json_decode( $body, true );

		return $json;
	}

	/**
	 * Get city and postal code
	 *
	 * @return array
	 */
	public function get_city_and_postal_code() {
		$data = $this->get_data();

		return [ 'city' => $data['city'], 'postal_code' => $data['postal'] ];
	}
}
