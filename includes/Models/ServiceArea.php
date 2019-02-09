<?php

namespace Stackonet\Models;

class ServiceArea {

	/**
	 * @var string
	 */
	private static $option = 'repair_service_area';

	/**
	 * Get options
	 *
	 * @return array
	 */
	private static function get_option() {
		$option = get_option( self::$option );

		return is_array( $option ) ? $option : [];
	}

	public static function get_zip_codes() {
		$options   = self::get_option();
		$zip_codes = wp_list_pluck( $options, 'zip_code' );
		$zip_codes = array_values( $zip_codes );
		$zip_codes = count( $zip_codes ) ? array_map( 'intval', $zip_codes ) : [];

		return array_values( $zip_codes );
	}

	/**
	 * Get service areas
	 *
	 * @return array
	 */
	public static function get_areas() {
		$options = self::get_option();

		return array_values( $options );
	}

	/**
	 * Get area by id
	 *
	 * @param string $id
	 *
	 * @return array
	 */
	public static function get_area( $id ) {
		$options = self::get_option();
		$ids     = wp_list_pluck( $options, 'id' );
		$index   = array_search( $id, $ids );

		if ( false === $index ) {
			return [];
		}

		return $options[ $index ];
	}

	/**
	 * Create new service area
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function create( array $data ) {
		$id   = uniqid();
		$data = wp_parse_args( $data, [
			'id'       => $id,
			'zip_code' => '',
			'address'  => '',
		] );

		$options        = self::get_option();
		$options[ $id ] = $data;

		update_option( self::$option, $options );

		return $data;
	}

	/**
	 * Update options
	 *
	 * @param string $id
	 * @param array $data
	 *
	 * @return array
	 */
	public static function update( $id, array $data ) {
		$options = self::get_option();
		$option  = isset( $options[ $id ] ) ? $options[ $id ] : [];
		if ( empty( $option ) ) {
			return self::create( $data );
		}

		$options[ $id ] = wp_parse_args( $data, $option );

		update_option( self::$option, $options );

		return $options[ $id ];
	}

	/**
	 * Delete option by id
	 *
	 * @param string $id
	 */
	public static function delete( $id ) {
		$options = self::get_option();

		if ( isset( $options[ $id ] ) ) {
			unset( $options[ $id ] );
		}

		update_option( self::$option, $options );
	}
}
