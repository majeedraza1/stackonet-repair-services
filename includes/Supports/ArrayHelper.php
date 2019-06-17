<?php

namespace Stackonet\Supports;

class ArrayHelper {
	/**
	 * Insert the given element after the given key in the array
	 *
	 * Sample usage:
	 *
	 * given:
	 * array( 'item_1' => 'foo', 'item_2' => 'bar' )
	 *
	 * ArrayHelper::insert_after( $array, 'item_1', array( 'item_1.5' => 'w00t' ) )
	 *
	 * becomes:
	 * array( 'item_1' => 'foo', 'item_1.5' => 'w00t', 'item_2' => 'bar' )
	 *
	 *
	 * @param array $array array to insert the given element into
	 * @param string $insert_key key to insert given element after
	 * @param array $element element to insert into array
	 *
	 * @return array
	 */
	public static function insert_after( array $array, $insert_key, array $element ) {
		$new_array = array();
		foreach ( $array as $key => $value ) {
			$new_array[ $key ] = $value;
			if ( $insert_key == $key ) {
				foreach ( $element as $k => $v ) {
					$new_array[ $k ] = $v;
				}
			}
		}

		return $new_array;
	}

	/**
	 * Create multidimensional array unique for any single key index.
	 *
	 * Sample usage:
	 *
	 * given:
	 *
	 * $details = array(
	 *      array("id"=>"1", "name"=>"Mike",    "num"=>"9876543210"),
	 *      array("id"=>"2", "name"=>"Carissa", "num"=>"08548596258"),
	 *      array("id"=>"1", "name"=>"Mathew",  "num"=>"784581254"),
	 * )
	 *
	 * ArrayHelper::unique_multidim_array( $details, 'id' )
	 *
	 * becomes:
	 * array(
	 *      array("id"=>"1","name"=>"Mike","num"=>"9876543210"),
	 *      array("id"=>"2","name"=>"Carissa","num"=>"08548596258"),
	 * )
	 *
	 * @param array $array
	 * @param string $key
	 *
	 * @return array
	 */
	public static function unique_multidim_array( array $array, $key ) {
		$temp_array = array();
		$i          = 0;
		$key_array  = array();

		foreach ( $array as $val ) {
			if ( ! in_array( $val[ $key ], $key_array ) ) {
				$key_array[ $i ]  = $val[ $key ];
				$temp_array[ $i ] = $val;
			}
			$i ++;
		}

		return $temp_array;
	}
}
