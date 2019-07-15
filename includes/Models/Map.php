<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class Map extends DatabaseModel {

	/**
	 * @var string
	 */
	protected $table = 'stackonet_map';

	/**
	 * Cache group name
	 *
	 * @var string
	 */
	protected $cache_group = 'stackonet_map';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'                     => 0,
		'title'                  => '',
		'formatted_base_address' => '',
		'base_address_latitude'  => '',
		'base_address_longitude' => '',
		'base_datetime'          => '',
		'place_text'             => '',
		'travel_mode'            => '',
		'places'                 => '',
		'created_by'             => 0,
		'created_at'             => '',
		'updated_at'             => '',
		'deleted_at'             => '',
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [
		'%d',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%d',
		'%s',
		'%s',
		'%s'
	];

	public function to_array() {
		$data           = parent::to_array();
		$data['places'] = $this->unserialize( $data['places'] );

		return $data;
	}

	public function find( $args = [] ) {
		$results = parent::find( $args );
		$items   = [];
		foreach ( $results as $result ) {
			$items[] = new self( $result );
		}

		return $items;
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		return [];
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
                `title` VARCHAR(255) DEFAULT NULL,
                `formatted_base_address` TEXT DEFAULT NULL,
                `base_address_latitude` VARCHAR(255) DEFAULT NULL,
                `base_address_longitude` VARCHAR(255) DEFAULT NULL,
                `base_datetime` datetime DEFAULT NULL,
                `place_text` VARCHAR(255) DEFAULT NULL,
                `travel_mode` VARCHAR(50) DEFAULT NULL,
                `places` LONGTEXT DEFAULT NULL,
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
