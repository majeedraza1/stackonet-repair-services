<?php

namespace Stackonet\Interfaces;

defined( 'ABSPATH' ) || exit;

interface IpServiceProviderInterface {

	/**
	 * Get data from service provider
	 *
	 * @return mixed
	 */
	public function get_data();

	/**
	 * Get city and postal code
	 *
	 * @return array
	 */
	public function get_city_and_postal_code();
}
