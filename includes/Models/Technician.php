<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class Technician extends DatabaseModel {

	/**
	 * @var string
	 */
	protected $table = 'stackonet_technician';

	/**
	 * Cache group name
	 *
	 * @var string
	 */
	protected $cache_group = 'stackonet_technician';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'         => 0,
		'first_name' => '',
		'last_name'  => '',
		'email'      => '',
		'phone'      => '',
		'resume_id'  => '',
		'extra_info' => '',
		'status'     => '',
		'created_at' => '',
		'updated_at' => '',
		'deleted_at' => null,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];

	/**
	 * Get available status
	 *
	 * @return array
	 */
	public static function available_status() {
		return [
			'active' => __( 'Active', 'stackonet-repair-services' ),
		];
	}

	/**
	 * Find multiple records from database
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primaryKey;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
			$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
		}

		$valid_status = array_keys( self::available_status() );
		$status       = isset( $args['status'] ) ? $args['status'] : 'all';
		if ( is_string( $status ) && in_array( $status, $valid_status ) ) {
			$query .= $wpdb->prepare( " AND status = %s", $status );
		}

		if ( is_array( $status ) ) {
			$query .= " AND (";
			foreach ( $status as $index => $m_stage ) {
				if ( $index !== 0 ) {
					$query .= " OR";
				}
				$query .= $wpdb->prepare( " status = %s", $m_stage );
			}
			$query .= ")";
		}

		if ( 'trash' == $status ) {
			$query .= " AND deleted_at IS NOT NULL";
		} else {
			$query .= " AND deleted_at IS NULL";
		}

		$query   .= " ORDER BY {$orderby} {$order}";
		$query   .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		$items = [];
		if ( $results ) {
			foreach ( $results as $result ) {
				$items[] = new self( $result );
			}
		}

		return $items;
	}

	/**
	 * Search phone
	 *
	 * @param array $args
	 * @param array $fields
	 *
	 * @return array
	 */
	public function search( $args, $fields = [] ) {
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$string = isset( $args['search'] ) ? esc_sql( $args['search'] ) : '';
		$fields = empty( $fields ) ? array_keys( $this->default_data ) : $fields;
		$status = ! empty( $args['status'] ) ? $args['status'] : 'all';

		$cache_key = sprintf( 'technician_search_%s', md5( json_encode( $args ) ) );
		$phones    = wp_cache_get( $cache_key, $this->cache_group );
		if ( false === $phones ) {
			$query = "SELECT * FROM {$table} WHERE 1 = 1";

			if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
				$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
			}

			if ( 'trash' == $status ) {
				$query .= " AND deleted_at IS NOT NULL";
			} else {
				$query .= " AND deleted_at IS NULL";
			}

			if ( in_array( $status, array_keys( self::available_status() ) ) ) {
				$query .= $wpdb->prepare( " AND status = %s", $status );
			}

			if ( isset( $args['technician_id__in'] ) && is_array( $args['technician_id__in'] ) ) {
				$ids   = array_map( function ( $id ) {
					$id = intval( $id );

					return $id > 0 ? $id : 0;
				}, $args['technician_id__in'] );
				$query .= " AND id IN(" . implode( ',', array_filter( $ids ) ) . ")";
			}
			$total_fields = count( $fields );
			foreach ( $fields as $index => $field ) {
				if ( 0 === $index ) {
					$query .= " AND ({$field} LIKE '%{$string}%'";
				} elseif ( ( $total_fields - 1 ) === $index ) {
					$query .= " OR {$field} LIKE '%{$string}%')";
				} else {
					$query .= " OR {$field} LIKE '%{$string}%'";
				}
			}
			$items = $wpdb->get_results( $query, ARRAY_A );
			if ( $items ) {
				foreach ( $items as $item ) {
					$phones[] = new self( $item );
				}
			}
			wp_cache_add( $cache_key, $phones, $this->cache_group );
		}

		return $phones;
	}

	/**
	 * Find record by id
	 *
	 * @param int $id
	 *
	 * @return self|false
	 */
	public function find_by_id( $id ) {
		$item = parent::find_by_id( $id );
		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$status = self::available_status();
		$counts = wp_cache_get( 'technician_records_count', $this->cache_group );
		if ( false === $counts ) {
			$query   = "SELECT status, COUNT( * ) AS num_entries FROM {$table} WHERE deleted_at IS NULL";
			$query   .= " GROUP BY status";
			$results = $wpdb->get_results( $query, ARRAY_A );
			foreach ( $status as $key => $life_stage ) {
				$counts[ $key ] = 0;
			}
			foreach ( $results as $row ) {
				$counts[ $row['status'] ] = intval( $row['num_entries'] );
			}
			$counts['all']   = array_sum( $counts );
			$query           = "SELECT COUNT( * ) AS num_entries FROM {$table} WHERE deleted_at IS NOT NULL";
			$results         = $wpdb->get_row( $query, ARRAY_A );
			$counts['trash'] = intval( $results['num_entries'] );
			wp_cache_set( 'technician_records_count', $counts, $this->cache_group );
		}

		return $counts;
	}

	/**
	 * Create database table
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `first_name` VARCHAR(100) DEFAULT NULL,
                `last_name` VARCHAR(100) DEFAULT NULL,
                `email` VARCHAR(100) DEFAULT NULL,
                `phone` VARCHAR(20) DEFAULT NULL,
                `resume_id` VARCHAR(20) DEFAULT NULL,
                `extra_info` LONGTEXT DEFAULT NULL,
                `status` varchar(50) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
