<?php

namespace Stackonet\Abstracts;

use ArrayAccess;
use JsonSerializable;

defined( 'ABSPATH' ) || exit;

class DatabaseModel implements ArrayAccess, JsonSerializable {
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [];

	/**
	 * The number of models to return for pagination.
	 *
	 * @var int
	 */
	protected $perPage = 15;

	/**
	 * @var string
	 */
	protected $cache_group;

	/**
	 * Model constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = [] ) {
		if ( $data ) {
			$this->data = $data;
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return json_encode( $this->to_array() );
	}

	/**
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->data;
	}

	/**
	 * Does this collection have a given key?
	 *
	 * @param string $key The data key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Set collection item
	 *
	 * @param string $key The data key
	 * @param mixed $value The data value
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Get collection item for key
	 *
	 * @param string $key The data key
	 * @param mixed $default The default value to return if data key does not exist
	 *
	 * @return mixed The key's value, or the default value
	 */
	public function get( $key, $default = null ) {
		return $this->has( $key ) ? $this->data[ $key ] : $default;
	}

	/**
	 * Remove item from collection
	 *
	 * @param string $key The data key
	 */
	public function remove( $key ) {
		if ( $this->has( $key ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Remove all items from collection
	 */
	public function clear() {
		$this->data = array();
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$orderby  = isset( $args['orderby'] ) ? esc_sql( $args['orderby'] ) : 'id';
		$order    = isset( $args['order'] ) ? esc_sql( $args['order'] ) : 'DESC';
		$offset   = isset( $args['offset'] ) ? intval( $args['offset'] ) : 0;
		$per_page = isset( $args['per_page'] ) ? intval( $args['per_page'] ) : 20;
		$trash    = isset( $args['trash'] ) ? $args['trash'] : false;
		$status   = isset( $args['status'] ) ? $args['status'] : null;
		$status   = in_array( $status, [ 'accept', 'reject' ] ) ? $status : 'any';
		$trash    = in_array( $trash, array( 'yes', 'on', '1', 1, true, 'true' ), true );

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( $trash ) {
			$query .= " AND deleted_at IS NOT NULL";
		} else {
			$query .= " AND deleted_at IS NULL";
		}

		if ( 'any' != $status ) {
			$query .= $wpdb->prepare( " AND status = %s", $status );
		}

		$query .= " ORDER BY {$orderby} {$order}";
		$query .= sprintf( " LIMIT %d OFFSET %d", $per_page, $offset );
		$items = $wpdb->get_results( $query, ARRAY_A );

		$data = [];
		if ( $items ) {
			foreach ( $items as $item ) {
				$data[] = new self( $item );
			}
		}

		return $data;
	}

	/**
	 * Find record by id
	 *
	 * @param int $id
	 *
	 * @return self|bool
	 */
	public function find_by_id( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$sql  = $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id );
		$item = $wpdb->get_row( $sql, ARRAY_A );
		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Create data
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function create( array $data ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$now   = current_time( 'mysql' );

		$_data = [];
		foreach ( $this->default_data as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $default;
		}
		unset( $_data['id'] );

		$_data['created_at'] = $now;
		$_data['updated_at'] = $now;

		$wpdb->insert( $table, $_data );

		return $wpdb->insert_id;
	}

	/**
	 * Update data
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function update( array $data ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$id    = isset( $data['id'] ) ? intval( $data['id'] ) : 0;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		$_data = [];
		foreach ( $this->default_data as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $item->get( $key );
		}

		$_data['id']         = $id;
		$_data['created_at'] = $item->get( 'created_at' );
		$_data['updated_at'] = current_time( 'mysql' );
		$_data['deleted_at'] = null;

		if ( $wpdb->update( $table, $_data, [ 'id' => $id ], $this->data_format, '%d' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete data
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete( $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		return ( false !== $wpdb->delete( $table, [ 'id' => intval( $id ) ], '%d' ) );
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists( $offset ) {
		return $this->has( $offset );
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public function offsetSet( $offset, $value ) {
		$this->set( $offset, $value );
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		$this->remove( $offset );
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return mixed data which can be serialized by json_encode
	 * which is a value of any type other than a resource.
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}

	/**
	 * Handle dynamic static method calls into the method.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $parameters ) {
		return ( new static )->$method( ...$parameters );
	}
}
