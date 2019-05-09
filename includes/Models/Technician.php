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
