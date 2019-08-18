<?php

namespace Stackonet\Integrations;

use Stackonet\Models\Appointment;
use Stackonet\Models\Settings;
use WC_Order;

class GoogleMap {

	/**
	 * Get latitude and longitude from Google
	 *
	 * @param string $address
	 *
	 * @return array
	 */
	public static function get_latitude_longitude( $address ) {
		$map_key  = Settings::get_map_api_key();
		$rest_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode( $address ) . '&key=' . $map_key;
		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );
		$lat_lan  = ! empty( $data['results'][0]['geometry']['location'] ) ? $data['results'][0]['geometry']['location'] : [];

		return $lat_lan;
	}

	/**
	 * @param int|float $latitude
	 * @param int|float $longitude
	 *
	 * @return array|mixed|object
	 */
	public static function get_formatted_address_from_lat_lng( $latitude, $longitude ) {
		$map_key  = Settings::get_map_api_key();
		$rest_url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&key=" . $map_key;
		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );
		$address  = isset( $data['results'][0] ) ? $data['results'][0] : [];

		return $address;
	}

	/**
	 * Get customer latitude and longitude from order
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_customer_latitude_longitude_from_order( WC_Order $order ) {
		$lat_lan = $order->get_meta( '_customer_latitude_longitude', true );
		if ( empty( $lat_lan ) ) {
			$address = $order->get_address( 'billing' );
			unset( $address['first_name'], $address['last_name'], $address['company'] );
			$address = WC()->countries->get_formatted_address( $address, ', ' );
			$lat_lan = self::get_latitude_longitude( $address );
			if ( ! empty( $lat_lan ) ) {
				$order->update_meta_data( '_customer_latitude_longitude', $lat_lan );
				$order->save_meta_data();
			}
		}

		return $lat_lan;
	}

	/**
	 * Get customer latitude and longitude from order
	 *
	 * @param Appointment $appointment
	 *
	 * @return array
	 */
	public static function get_appointment_latitude_longitude( Appointment $appointment ) {
		$latitude  = $appointment->get( 'latitude' );
		$longitude = $appointment->get( 'longitude' );
		if ( empty( $latitude ) || empty( $longitude ) ) {
			$address = $appointment->get( 'full_address' );
			if ( ! empty( $address ) ) {
				$lat_lan = self::get_latitude_longitude( $address );
			}
			if ( ! empty( $lat_lan ) ) {
				$latitude  = (string) $lat_lan['lat'];
				$longitude = (string) $lat_lan['lng'];
				$appointment->update( [
					'id'        => $appointment->get( 'id' ),
					'latitude'  => $latitude,
					'longitude' => $longitude,
				] );
			}
		}

		return [ 'lat' => floatval( $latitude ), 'lng' => floatval( $longitude ) ];
	}
}
