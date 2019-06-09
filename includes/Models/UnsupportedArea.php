<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Interfaces\DataStoreInterface;

defined( 'ABSPATH' ) || exit;

class UnsupportedArea extends DatabaseModel implements DataStoreInterface {

	const TABLE_NAME = 'unsupported_areas';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'unsupported_areas';

	/**
	 * Default Data
	 *
	 * @var array
	 */
	protected $default_data = array(
		'id'           => 0,
		'user_id'      => 0,
		'user_ip'      => '127.0.0.1',
		'user_agent'   => null,
		'email'        => null,
		'phone'        => null,
		'zip_code'     => null,
		'device_title' => null,
		'device_model' => null,
		'device_color' => null,
		'status'       => 'unread',
		'created_at'   => null,
		'deleted_at'   => null,
	);

	/**
	 * Message data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', ];

	/**
	 * Available status for the model
	 *
	 * @var array
	 */
	protected $available_status = [ 'all', 'unread', 'read', 'trash' ];

	/**
	 * Find entries
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = array() ) {
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primaryKey;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		$status = isset( $args['status'] ) ? $args['status'] : null;
		$status = in_array( $status, $this->available_status ) ? $status : 'all';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( 'trash' == $status ) {
			$query .= " AND deleted_at IS NOT NULL";
		} else {
			$query .= " AND deleted_at IS NULL";
		}

		if ( ! in_array( $status, [ 'all', 'trash' ] ) ) {
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
	 * Get entry by entry ID
	 *
	 * @param int $id
	 *
	 * @return self|false
	 */
	public function find_by_id( $id ) {
		$items = parent::find_by_id( $id );

		if ( ! $items ) {
			return false;
		}

		return new self( $items );
	}

	/**
	 * Insert a row into a entries table with meta values.
	 *
	 * @param array $data Form data in $key => 'value' format
	 * array( $key1 => 'value 1', $key2 => 'value 2' )
	 *
	 * @return int last insert id
	 */
	public function create( array $data ) {
		$data['user_id']    = get_current_user_id();
		$data['user_ip']    = self::get_remote_ip();
		$data['user_agent'] = self::get_user_agent();
		$data['created_at'] = current_time( 'mysql' );

		return parent::create( $data );
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
		$id    = isset( $data[ $this->primaryKey ] ) ? intval( $data[ $this->primaryKey ] ) : 0;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		$_data = [];
		foreach ( $this->default_data as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $item->get( $key );
		}

		$_data[ $this->primaryKey ] = $id;
		$_data['created_at']        = $item->get( 'created_at' );
		$_data['deleted_at']        = isset( $data['deleted_at'] ) ? $data['deleted_at'] : null;

		if ( $wpdb->update( $table, $_data, [ $this->primaryKey => $id ], $this->data_format, $this->primaryKeyType ) ) {
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
		if ( ! $id ) {
			$id = $this->get( 'id' );
		}
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		return ( false !== $wpdb->delete( $table, [ 'id' => intval( $id ) ], '%d' ) );
	}

	/**
	 * Trash data
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function trash( $id = 0 ) {
		if ( ! $id ) {
			$id = $this->get( 'id' );
		}

		return $this->update( [
			'id'         => $id,
			'deleted_at' => current_time( 'mysql' ),
		] );
	}

	/**
	 * Restore data
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function restore( $id = 0 ) {
		if ( ! $id ) {
			$id = $this->get( 'id' );
		}

		return $this->update( [
			'id'         => $id,
			'deleted_at' => null,
		] );
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$counts = array_fill_keys( $this->available_status, 0 );

		$query   = "SELECT status, COUNT( status ) AS num_posts FROM {$table}";
		$query   .= ' WHERE deleted_at IS NULL GROUP BY status';
		$results = $wpdb->get_results( $query, ARRAY_A );

		$query2   = "SELECT COUNT( deleted_at ) AS num_posts FROM {$table}";
		$query2   .= ' WHERE deleted_at IS NOT NULL GROUP BY deleted_at';
		$results2 = $wpdb->get_row( $query2, ARRAY_A );

		$counts['trash'] = isset( $results2['num_posts'] ) ? intval( $results2['num_posts'] ) : 0;


		foreach ( $results as $row ) {
			$counts[ $row['status'] ] = intval( $row['num_posts'] );
		}

		$counts['all'] = ( $counts['read'] + $counts['unread'] );

		return $counts;
	}

	/**
	 * Create table
	 */
	public function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `user_ip` varchar(45) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(50) DEFAULT NULL,
                `zip_code` varchar(255) DEFAULT NULL,
                `device_title` varchar(255) DEFAULT NULL,
                `device_model` varchar(255) DEFAULT NULL,
                `device_color` varchar(255) DEFAULT NULL,
                `status` varchar(20) DEFAULT 'unread',
                `created_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );

		$this->add_table_columns();
	}

	/**
	 * Add new columns to table
	 */
	public function add_table_columns() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$version    = get_option( 'stackonet_unsupported_areas_table_version' );
		$version    = ! empty( $version ) ? $version : '1.0.0';

		if ( version_compare( $version, '1.01', '<' ) ) {
			$row = $wpdb->get_row( "SELECT * FROM {$table_name}", ARRAY_A );
			if ( ! isset( $row['brand'] ) ) {
				$wpdb->query( "ALTER TABLE {$table_name} ADD `phone` VARCHAR(50) NULL DEFAULT NULL AFTER `email`" );
			}

			update_option( 'stackonet_unsupported_areas_table_version', '1.0.1' );
		}
	}

	/**
	 * Get user IP address
	 *
	 * @return string
	 */
	public static function get_remote_ip() {
		$server_ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return $_SERVER[ $key ];
			}
		}

		// Fallback local ip.
		return '127.0.0.1';
	}

	/**
	 * Get user browser name
	 *
	 * @return string
	 */
	public static function get_user_agent() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
		}

		return '';
	}
}
