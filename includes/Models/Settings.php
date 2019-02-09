<?php

namespace Stackonet\Models;

class Settings {

	/**
	 * @var string
	 */
	private static $option = 'repair_services_settings';

	private static $options_keys = [
		'google_map_key' => '',
	];

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
	 * Get map api key
	 *
	 * @return string
	 */
	public static function get_map_api_key() {
		$options = self::get_option();
		$map_key = 'google_map_key';

		return ! empty( $options[ $map_key ] ) ? wp_strip_all_tags( $options[ $map_key ] ) : '';
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::get_option();
	}

	/**
	 * Update settings
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function update( array $data ) {
		$_data = [];
		foreach ( self::$options_keys as $options_key => $default ) {
			$_data[ $options_key ] = isset( $data[ $options_key ] ) ? $data[ $options_key ] : $default;
		}
		update_option( self::$option, $_data );

		return $data;
	}
}
