<?php

namespace Stackonet\Models;

class DeviceIssue {

	/**
	 * @var string
	 */
	private static $option = 'device_issues';

	/**
	 * Default data
	 *
	 * @var array
	 */
	private static $default = [
		'id'    => '',
		'title' => '',
		'price' => '',
	];

	/**
	 * Prepare item for database
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function prepare_item_for_database( array $data ) {
		$data = wp_parse_args( $data, self::$default );

		$sanitize_data = [
			'id'    => sanitize_text_field( $data['id'] ),
			'title' => sanitize_text_field( $data['title'] ),
			'price' => floatval( $data['price'] ),
		];

		return $sanitize_data;
	}

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
	 * Get issues
	 *
	 * @return array
	 */
	public static function get_issues() {
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
	public static function get_issue( $id ) {
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
			'id'    => $id,
			'title' => '',
			'price' => '',
		] );

		$data = self::prepare_item_for_database( $data );

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
			self::create( $data );
		}

		$data = wp_parse_args( $data, $option );
		$data = self::prepare_item_for_database( $data );

		$options[ $id ] = $data;

		update_option( self::$option, $options );

		return $data;
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
