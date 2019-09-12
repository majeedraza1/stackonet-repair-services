<?php

namespace Stackonet\Integrations;

use Stackonet\Models\Appointment;
use Stackonet\Models\GoogleNearbyPlace;
use Stackonet\Models\GooglePlace;
use Stackonet\Models\Settings;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class GoogleMap {

	/**
	 * Get latitude and longitude from Google
	 *
	 * @param string $address
	 *
	 * @return array
	 */
	public static function get_latitude_longitude( $address ) {
		$GooglePlace = GooglePlace::get_address_from_formatted_address( $address );
		if ( $GooglePlace instanceof GooglePlace ) {
			$addressObject = $GooglePlace->to_array();
		} else {
			$map_key       = Settings::get_map_api_key();
			$rest_url      = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode( $address ) . '&key=' . $map_key;
			$response      = wp_remote_get( $rest_url );
			$body          = wp_remote_retrieve_body( $response );
			$data          = json_decode( $body, true );
			$addressObject = ! empty( $data['results'][0] ) ? $data['results'][0] : [];

			if ( $addressObject ) {
				GooglePlace::add_place_data_if_not_exist( $addressObject );
			}
		}

		return ! empty( $addressObject['geometry']['location'] ) ? $addressObject['geometry']['location'] : [];
	}

	/**
	 * Get address from place id
	 *
	 * @param string $place_id
	 *
	 * @return array
	 */
	public static function get_address_from_place_id( $place_id ) {
		$address = GooglePlace::get_place_from_place_id( $place_id );
		if ( $address instanceof GooglePlace ) {
			return $address->to_array();
		}

		$map_key  = Settings::get_map_api_key();
		$rest_url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=" . $place_id . "&key=" . $map_key;
		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );
		$address  = isset( $data['result'] ) ? $data['result'] : [];

		GooglePlace::add_place_data_if_not_exist( $address );

		return $address;
	}

	/**
	 * @param int|float $latitude
	 * @param int|float $longitude
	 *
	 * @return array|mixed|object
	 */
	public static function get_address_from_lat_lng( $latitude, $longitude ) {
		$address = GooglePlace::get_address_from_lat_lng( $latitude, $longitude );
		if ( $address instanceof GooglePlace ) {
			return $address->to_array();
		}

		$map_key  = Settings::get_map_api_key();
		$rest_url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&key=" . $map_key;
		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );
		$address  = isset( $data['results'][0] ) ? $data['results'][0] : [];

		GooglePlace::add_place_data_if_not_exist( $address );

		return $address;
	}

	/**
	 * @param $latitude
	 * @param $longitude
	 *
	 * @return array|mixed|object
	 */
	public static function nearby_search( $latitude, $longitude ) {
		$places = GoogleNearbyPlace::get_places_from_lat_lng( $latitude, $longitude );
		if ( $places instanceof GoogleNearbyPlace ) {
			return $places->to_array();
		}

		$rest_url = add_query_arg( [
			'key'      => Settings::get_map_api_key(),
			'location' => $latitude . "," . $longitude,
			'rankby'   => 'distance',
			// 'radius'   => 20, // Radius won't work with rankby
		], "https://maps.googleapis.com/maps/api/place/nearbysearch/json" );

		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );
		$address  = isset( $data['results'] ) ? $data['results'] : [];
		GoogleNearbyPlace::add_place_data_if_not_exist( $latitude, $longitude, $address );

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

	/**
	 * Get snappedPoints using Google Map snapToRoads API
	 *
	 * @param array $logs It can take only 100 logs entries
	 *
	 * @return array
	 */
	public static function get_snapped_points( array $logs ) {
		if ( count( $logs ) <= 100 ) {
			$points = self::get_snapped_points_for_chunk( $logs );

			return $points;
		}

		$chunks = array_chunk( $logs, 100 );
		$points = [];

		foreach ( $chunks as $chunk ) {
			$points = array_merge( $points, self::get_snapped_points_for_chunk( $chunk ) );
		}

		return $points;
	}

	/**
	 * Get snappedPoints using Google Map snapToRoads API
	 *
	 * @param array $logs It can take only 100 logs entries
	 *
	 * @return array
	 */
	private static function get_snapped_points_for_chunk( array $logs ) {
		$path = [];
		foreach ( $logs as $log ) {
			if ( empty( $log['latitude'] ) || empty( $log['longitude'] ) ) {
				continue;
			}
			$path[] = sprintf( "%s,%s", $log['latitude'], $log['longitude'] );
		}

		$path = implode( "|", $path );

		$transient_name       = 'snap_to_roads_' . md5( $path );
		$transient_expiration = DAY_IN_SECONDS;
		$points               = get_transient( $transient_name );

		if ( false !== $points ) {
			return $points;
		}

		$points = [];

		$url = add_query_arg( [
			'key'         => Settings::get_map_api_key(),
			'path'        => $path,
			'interpolate' => true,
		], 'https://roads.googleapis.com/v1/snapToRoads' );

		$response = wp_remote_get( $url );
		if ( ! is_wp_error( $response ) ) {
			$body    = wp_remote_retrieve_body( $response );
			$objects = json_decode( $body, true );
			$points  = isset( $objects['snappedPoints'] ) ? $objects['snappedPoints'] : [];

			set_transient( $transient_name, $points, $transient_expiration );
		}

		return $points;
	}

	/**
	 * Get distance between two places
	 *
	 * @param float $latitudeFrom
	 * @param float $longitudeFrom
	 * @param float $latitudeTo
	 * @param float $longitudeTo
	 *
	 * @return array
	 */
	public static function get_distance( $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo ) {
		$map_key  = Settings::get_map_api_key();
		$rest_url = add_query_arg( [
			'origins'        => $latitudeFrom . ',' . $longitudeFrom,
			'destinations'   => $latitudeTo . ',' . $longitudeTo,
			'departure_time' => 'now',
			'mode'           => 'walking',
			'units'          => 'metric',
			'key'            => $map_key,
		], 'https://maps.googleapis.com/maps/api/distancematrix/json' );
		$response = wp_remote_get( $rest_url );
		$body     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body, true );

		return $data;
	}
}
