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
		'status'              => 'publish',
	);

	/**
	 * @var string
	 */
	private static $option = 'repair_service_device';

	/**
	 * @param array $item
	 *
	 * @return array
	 */
	public static function format_item_for_response( $item ) {
		if ( is_numeric( $item['device_image'] ) ) {
			$image = wp_get_attachment_image_src( intval( $item['device_image'] ), 'full' );

			$item['image'] = [
				'id'     => absint( $item['device_image'] ),
				'src'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2]
			];
		}

		return $item;
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
	 * Get devices
	 *
	 * @return array
	 */
	public static function get_devices() {
		$options  = self::get_option();
		$response = [];

		if ( count( $options ) < 1 ) {
			return $response;
		}

		foreach ( $options as $option ) {
			$response[] = self::format_item_for_response( $option );
		}

		return $response;
	}

	/**
	 * Get device
	 *
	 * @param string|int $id unique device id
	 *
	 * @return array
	 */
	public static function get_device( $id ) {
		$options   = self::get_option();
		$ids       = wp_list_pluck( $options, 'id' );
		$index     = array_search( $id, $ids );
		$hasDevice = false !== $index;

		return $hasDevice ? self::format_item_for_response( $options[ $index ] ) : [];
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

	public static function update( array $data ) {
		$options   = self::get_option();
		$ids       = wp_list_pluck( $options, 'id' );
		$index     = array_search( $data['id'], $ids );
		$hasDevice = false !== $index;

		if ( ! $hasDevice ) {
			return $data;
		}

		$options[ $index ] = wp_parse_args( $data, $options[ $index ] );

		update_option( self::$option, $options );

		return $data;
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
}
