<?php


namespace Stackonet\Models;


class SmsTemplate {
	/**
	 * Default data
	 *
	 * @var array
	 */
	private static $default = array(
		'id'      => 0,
		'content' => '',
	);

	/**
	 * @var string
	 */
	private static $option = 'stackonet_sms_templates';

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
	public static function get_templates() {
		$options  = self::get_option();
		$response = [];

		if ( count( $options ) < 1 ) {
			return $response;
		}

		$response = $options;

		return $response;
	}

	/**
	 * Get device
	 *
	 * @param string|int $id unique device id
	 *
	 * @return array
	 */
	public static function get_template( $id ) {
		$options   = self::get_option();
		$ids       = wp_list_pluck( $options, 'id' );
		$index     = array_search( $id, $ids );
		$hasDevice = false !== $index;

		return $hasDevice ? self::prepare_item_for_response( $options[ $index ] ) : [];
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function create( array $data ) {
		$options = self::get_option();

		$data       = wp_parse_args( $data, self::$default );
		$data['id'] = count( $options ) + 1;


		$sanitize_data = self::prepare_item_for_database( $data );
		$options[]     = $sanitize_data;

		update_option( self::$option, $options );

		return self::prepare_item_for_response( $sanitize_data );
	}

	/**
	 * Update data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function update( array $data ) {
		$options   = self::get_option();
		$ids       = wp_list_pluck( $options, 'id' );
		$index     = array_search( $data['id'], $ids );
		$hasDevice = false !== $index;

		if ( ! $hasDevice ) {
			return $data;
		}

		$data              = wp_parse_args( $data, $options[ $index ] );
		$sanitize_data     = self::prepare_item_for_database( $data );
		$options[ $index ] = $sanitize_data;

		update_option( self::$option, $options );

		return self::prepare_item_for_response( $data );
	}

	/**
	 * Delete device from database
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function delete( $id ) {
		$options = self::get_option();
		$ids     = wp_list_pluck( $options, 'id' );
		$index   = array_search( $id, $ids );
		if ( false !== $index ) {
			array_splice( $options, $index, 1 );
			update_option( self::$option, $options );

			return true;
		}

		return false;
	}

	private static function prepare_item_for_response( $data ) {
		return $data;
	}

	private static function prepare_item_for_database( array $data ) {
		return $data;
	}
}
