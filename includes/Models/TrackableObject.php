<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class TrackableObject extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_trackable_object';

	/**
	 * @var string
	 */
	protected $cache_group = 'stackonet_trackable_object';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'          => 0,
		'object_id'   => null,
		'object_name' => null,
		'object_type' => null,
		'object_icon' => null,
		'active'      => 0,
		'created_by'  => 0,
		'created_at'  => null,
		'updated_at'  => null,
		'deleted_at'  => null,
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ];

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$data         = parent::to_array();
		$data['icon'] = $this->get_object_icon();

		return $data;
	}

	/**
	 * Get object icon
	 *
	 * @return string|null
	 */
	public function get_object_icon() {
		$icon_id = intval( $this->get( 'object_icon' ) );
		if ( $icon_id ) {
			$src = wp_get_attachment_image_src( $icon_id );

			return isset( $src[0] ) ? $src[0] : null;
		}

		return null;
	}

	/**
	 * Find multiple records from database
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$results = parent::find( $args );
		$items   = [];
		foreach ( $results as $result ) {
			$items[] = new self( $result );
		}

		return $items;
	}

	/**
	 * Find record by id
	 *
	 * @param int $id
	 *
	 * @return array|self
	 */
	public function find_by_id( $id ) {
		$item = parent::find_by_id( $id );

		return new self( $item );
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
                `object_id` VARCHAR(20) DEFAULT NULL,
                `object_name` VARCHAR(100) DEFAULT NULL,
                `object_type` VARCHAR(50) DEFAULT NULL,
                `object_icon` VARCHAR(50) DEFAULT NULL,
                `active` tinyint(1) DEFAULT 0,
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
