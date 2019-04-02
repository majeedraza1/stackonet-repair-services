<?php

namespace Stackonet\Integrations;

use Stackonet\Supports\Logger;

class FirebaseDynamicLinks {

	/**
	 * URL to be shorten
	 *
	 * @var string
	 */
	protected $long_url = '';

	/**
	 * Firebase api key
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Firebase URL prefix or domain
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 * Shortens a given URL via Firebase Dynamic Links.
	 *
	 * @link https://firebase.google.com/products/dynamic-links/
	 * @link https://firebase.google.com/docs/dynamic-links/rest
	 *
	 * @param string $url URL to shorten
	 *
	 * @return string shortened URL
	 */
	public function shorten_url( $url = '' ) {

		$api_url = add_query_arg( [ 'key' => $this->get_api_key() ],
			'https://firebasedynamiclinks.googleapis.com/v1/shortLinks' );

		/**
		 * Query parameters
		 *
		 * @see https://firebase.google.com/docs/dynamic-links/rest
		 */
		$parameters = array(
			'dynamicLinkInfo' => [
				'domainUriPrefix' => $this->get_domain(),
				'link'            => untrailingslashit( $url ),
			],
			'suffix'          => array( 'option' => 'SHORT', ),
		);

		$post_args = array(
			'method'      => 'POST',
			'timeout'     => '10',
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => true,
			'headers'     => array(
				'content-type' => 'application/json',
			),
			'body'        => json_encode( $parameters ),
		);

		$response = wp_safe_remote_post( $api_url, $post_args );

		return $this->parse_url_shortening_response( $url, $response );
	}

	/**
	 * Parses a response from a URL shortening service.
	 *
	 * @param string $long_url the original long URL to return in case of errors
	 * @param array|\WP_Error $response
	 *
	 * @return string URL (shortened on success, or original long form on failure, while logging errors)
	 */
	public function parse_url_shortening_response( $long_url, $response ) {
		if ( $response instanceof \WP_Error ) {
			Logger::log( $response->get_error_message() );

			return $long_url;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		Logger::log( $data );

		if ( ! empty( $data['shortLink'] ) && is_string( $data['shortLink'] ) ) {
			return $data['shortLink'];
		}

		// Google Shorten error
		$error_message = '';
		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			if ( ! empty( $data['error']['errors'] ) ) {

				foreach ( $data['error']['errors'] as $error ) {
					// append an error message if it was provided
					if ( ! empty( $error['message'] ) ) {
						$error_message .= ': ' . $error['message'];
					}
					// append the reason code if it was provided
					if ( ! empty( $error['reason'] ) ) {
						$error_message .= ' (' . $error['reason'] . ')';
					}
					Logger::log( $error_message );
				}

			} else {
				// unknown error or maybe Firebase error
				Logger::log( is_array( $data['error'] ) && isset( $data['error']['message'] ) ? $data['error']['message'] : '' );
			}

			return $long_url;
		}

		if ( ! empty( $data['error'] ) ) {
			Logger::log( is_array( $data['error'] ) && isset( $data['error']['message'] ) ? $data['error']['message'] : $data['error'] );
		}

		return $long_url;
	}

	/**
	 * @return string
	 */
	public function get_domain() {
		return $this->domain;
	}

	/**
	 * @param string $domain
	 *
	 * @return self
	 */
	public function set_domain( $domain ) {
		$domain = str_replace( array( 'http://', 'https://' ), '', untrailingslashit( $domain ) );
		$domain = 'https://' . $domain;

		if ( filter_var( $domain, FILTER_VALIDATE_URL ) ) {
			$this->domain = $domain;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	protected function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Set API key
	 *
	 * @param string $api_key
	 *
	 * @return self
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;

		return $this;
	}
}
