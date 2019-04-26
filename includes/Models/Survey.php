<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class Survey extends DatabaseModel {

	/**
	 * @var string
	 */
	protected $table = 'stackonet_survey';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'            => 0,
		'latitude'      => '',
		'longitude'     => '',
		'full_address'  => '',
		'address'       => '',
		'device_status' => '',
		'created_by'    => '',
		'created_at'    => '',
		'updated_at'    => '',
		'deleted_at'    => null,
	];

	/**
	 * Get available status
	 *
	 * @return array
	 */
	public static function available_status() {
		return [
			'not-pertain'    => __( 'Not Pertain', 'stackonet-repair-services' ),
			'affordable'     => __( 'Affordable', 'stackonet-repair-services' ),
			'not-affordable' => __( 'Not Affordable', 'stackonet-repair-services' ),
		];
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$_data = parent::to_array();
		$data  = [];
		foreach ( $_data as $key => $value ) {
			$data[ $key ] = maybe_unserialize( $value );
		}

		return $data;
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
		$counts = wp_cache_get( 'survey_records_count', $this->cache_group );
		if ( false === $counts ) {
			$query   = "SELECT device_status, COUNT( * ) AS num_entries FROM {$table} WHERE deleted_at IS NULL";
			$query   .= " GROUP BY device_status";
			$results = $wpdb->get_results( $query, ARRAY_A );
			foreach ( $status as $key => $life_stage ) {
				$counts[ $key ] = 0;
			}
			foreach ( $results as $row ) {
				$counts[ $row['device_status'] ] = intval( $row['num_entries'] );
			}
			$counts['all']   = array_sum( $counts );
			$query           = "SELECT COUNT( * ) AS num_entries FROM {$table} WHERE deleted_at IS NOT NULL";
			$results         = $wpdb->get_row( $query, ARRAY_A );
			$counts['trash'] = intval( $results['num_entries'] );
			wp_cache_set( 'survey_records_count', $counts, $this->cache_group );
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
                `latitude` varchar(50) DEFAULT NULL,
                `longitude` varchar(50) DEFAULT NULL,
                `full_address` TEXT DEFAULT NULL,
                `address` LONGTEXT DEFAULT NULL,
                `device_status` varchar(50) DEFAULT NULL,
                `created_by` bigint(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}
}
