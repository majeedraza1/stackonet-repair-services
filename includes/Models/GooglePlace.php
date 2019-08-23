<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

defined( 'ABSPATH' ) || exit;

class GooglePlace extends DatabaseModel {

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'address_components'         => '',
		'formatted_address'          => '',
		'formatted_phone_number'     => '',
		'geometry'                   => '',
		'icon'                       => '',
		'id'                         => '',
		'international_phone_number' => '',
		'name'                       => '',
		'place_id'                   => '',
		'rating'                     => '',
		'reference'                  => '',
		'reviews'                    => '',
		'types'                      => '',
		'url'                        => '',
		'utc_offset'                 => '',
		'vicinity'                   => '',
		'website'                    => '',
	];

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'place_id';

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		return [];
	}

	/**
	 * Create database table
	 *
	 * @return void
	 */
	public function create_table() {
		// TODO: Implement create_table() method.
	}
}
