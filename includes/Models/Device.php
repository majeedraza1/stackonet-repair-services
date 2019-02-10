<?php

namespace Stackonet\Models;

class Device {

	/**
	 * Default data
	 *
	 * @var array
	 */
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
	 * Prepare item for response
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	public static function prepare_item_for_response( $item ) {
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
	 * Prepare item for database
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function prepare_item_for_database( array $data ) {

		$data = wp_parse_args( $data, self::$default );

		$sanitize_data = [
			'id'                  => sanitize_text_field( $data['id'] ),
			'product_id'          => absint( $data['product_id'] ),
			'device_title'        => sanitize_text_field( $data['device_title'] ),
			'device_image'        => absint( $data['device_image'] ),
			'broken_screen_label' => sanitize_text_field( $data['broken_screen_label'] ),
			'broken_screen_price' => floatval( $data['broken_screen_price'] ),
			'device_models'       => [],
			'multi_issues'        => [],
			'no_issues'           => [],
			'status'              => sanitize_text_field( $data['status'] ),
		];

		$device_models = [];
		$multi_issues  = [];
		$no_issues     = [];

		foreach ( $data['device_models'] as $index => $model ) {
			$device_models[ $index ]['title'] = sanitize_text_field( $model['title'] );
			foreach ( $model['colors'] as $color_index => $color ) {
				$device_models[ $index ]['colors'][ $color_index ] = [
					'color'    => sanitize_hex_color( $color['color'] ),
					'title'    => sanitize_text_field( $color['title'] ),
					'subtitle' => sanitize_text_field( $color['subtitle'] ),
				];
			}
		}

		foreach ( $data['multi_issues'] as $index => $issue ) {
			$multi_issues[ $index ] = [
				'id'    => sanitize_text_field( $issue['id'] ),
				'title' => sanitize_text_field( $issue['title'] ),
				'price' => floatval( $issue['price'] ),
			];
		}

		foreach ( $data['no_issues'] as $index => $issue ) {
			$no_issues[ $index ] = [
				'id'    => sanitize_text_field( $issue['id'] ),
				'title' => sanitize_text_field( $issue['title'] ),
				'price' => floatval( $issue['price'] ),
			];
		}

		$sanitize_data['device_models'] = $device_models;
		$sanitize_data['multi_issues']  = $multi_issues;
		$sanitize_data['no_issues']     = $no_issues;

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
			$response[] = self::prepare_item_for_response( $option );
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

		return $hasDevice ? self::prepare_item_for_response( $options[ $index ] ) : [];
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function create( array $data ) {
		$data       = wp_parse_args( $data, self::$default );
		$data['id'] = uniqid();

		$options = self::get_option();

		$sanitize_data = self::prepare_item_for_database( $data );
		$options[]     = $sanitize_data;

		update_option( self::$option, $options );

		return self::prepare_item_for_response( $sanitize_data );
	}

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
}
