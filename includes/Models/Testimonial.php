<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

defined( 'ABSPATH' ) || exit;

class Testimonial extends DatabaseModel {

	/**
	 * @var string
	 */
	protected $table = 'client_testimonial';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'          => 0,
		'name'        => '',
		'email'       => '',
		'phone'       => '',
		'description' => '',
		'rating'      => '',
		'status'      => '',
		'created_at'  => '',
		'updated_at'  => '',
		'deleted_at'  => null,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', ];

	/**
	 * Create table
	 */
	public function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `description` LONGTEXT DEFAULT NULL,
                `rating` int(1) unsigned DEFAULT NULL,
                `status` varchar(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
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
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		return [];
	}
}
