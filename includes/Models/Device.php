<?php

namespace Stackonet\Models;

class Device {

	private static $default = array(
		'id'                  => 0,
		'product_id'          => 0,
		'device_title'        => '',
		'device_image'        => '',
		'broken_screen_label' => '',
		'broken_screen_price' => '',
		'device_models'       => [],
		'multi_issues'        => [],
		'no_issues'           => [],
	);

	/**
	 * @var string
	 */
	private static $option = 'repair_service_device';

	/**
	 * Get options
	 *
	 * @return array
	 */
	private static function get_option() {
		$option = get_option( self::$option );

		return is_array( $option ) ? $option : [];
	}

	/**
	 * Get devices
	 *
	 * @return array
	 */
	public static function get_devices() {
		return array_values( self::get_option() );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function create( array $data ) {
		$data       = wp_parse_args( $data, self::$default );
		$data['id'] = uniqid();

		$options   = self::get_option();
		$options[] = $data;

		update_option( self::$option, $options );

		return $data;
	}
}
