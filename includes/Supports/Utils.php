<?php

namespace Stackonet\Supports;

defined( 'ABSPATH' ) || exit;

class Utils {

	/**
	 * Return the values from a single column in the input array
	 *
	 * @param array $array A multi-dimensional array (record set) from which to pull a column of values.
	 * @param mixed $column The column of values to return.
	 * @param mixed $index_key [optional] The column to use as the index/keys for the returned array.
	 *
	 * @return array
	 */
	public static function array_column( array $array, $column, $index_key = null ) {
		if ( function_exists( 'array_column' ) ) {
			return array_column( $array, $column, $index_key );
		}

		if ( function_exists( 'wp_list_pluck' ) ) {
			return wp_list_pluck( $array, $column, $index_key );
		}

		$result = array();
		foreach ( $array as $subArray ) {
			if ( is_null( $index_key ) && array_key_exists( $column, $subArray ) ) {
				$result[] = is_object( $subArray ) ? $subArray->$column : $subArray[ $column ];
			} elseif ( array_key_exists( $index_key, $subArray ) ) {
				if ( is_null( $column ) ) {
					$index            = is_object( $subArray ) ? $subArray->$index_key : $subArray[ $index_key ];
					$result[ $index ] = $subArray;
				} elseif ( array_key_exists( $column, $subArray ) ) {
					$index            = is_object( $subArray ) ? $subArray->$index_key : $subArray[ $index_key ];
					$result[ $index ] = is_object( $subArray ) ? $subArray->$column : $subArray[ $column ];
				}
			}
		}

		return $result;
	}
}
