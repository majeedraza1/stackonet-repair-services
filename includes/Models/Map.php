<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use WP_User;

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
		'support_ticket_id'      => '',
		'assigned_users'         => '',
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
		'%d',
		'%s',
		'%s',
		'%s'
	];

	/**
	 * @var WP_User
	 */
	private $user;

	public function to_array() {
		$data = parent::to_array();

		$data['id']                     = intval( $data['id'] );
		$data['support_ticket_id']      = intval( $data['support_ticket_id'] );
		$data['base_address_latitude']  = floatval( $data['base_address_latitude'] );
		$data['base_address_longitude'] = floatval( $data['base_address_longitude'] );
		$data['created_by']             = $this->author()->ID;
		$data['author']                 = [
			'display_name' => $this->author()->display_name,
		];

		return $data;
	}

	/**
	 * @return bool|WP_User
	 */
	public function author() {
		$created_by = $this->get( 'created_by' );
		if ( ! $this->user instanceof WP_User ) {
			$this->user = get_user_by( 'id', $created_by );
		}

		return $this->user;
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
                `title` VARCHAR(255) DEFAULT NULL,
                `formatted_base_address` TEXT DEFAULT NULL,
                `base_address_latitude` VARCHAR(255) DEFAULT NULL,
                `base_address_longitude` VARCHAR(255) DEFAULT NULL,
                `base_datetime` datetime DEFAULT NULL,
                `place_text` VARCHAR(255) DEFAULT NULL,
                `travel_mode` VARCHAR(50) DEFAULT NULL,
                `places` LONGTEXT DEFAULT NULL,
                `support_ticket_id` bigint(20) DEFAULT NULL,
                `assigned_users` TEXT DEFAULT NULL,
                `created_by` bigint(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
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

		$row = $wpdb->get_row( "SELECT * FROM {$table_name}", ARRAY_A );
		if ( ! isset( $row['support_ticket_id'] ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD `support_ticket_id` bigint(20) NULL DEFAULT NULL AFTER `places`" );
		}
		if ( ! isset( $row['assigned_users'] ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD `assigned_users` TEXT NULL DEFAULT NULL AFTER `support_ticket_id`" );
		}
	}
}
