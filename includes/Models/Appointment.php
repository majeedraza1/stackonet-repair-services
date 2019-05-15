<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;

class Appointment extends DatabaseModel {

	/**
	 * @var string
	 */
	protected $table = 'stackonet_spot_appointment';

	/**
	 * Cache group name
	 *
	 * @var string
	 */
	protected $cache_group = 'stackonet_spot_appointment';

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
                `brand` VARCHAR(100) DEFAULT NULL,
                `gadget` VARCHAR(100) DEFAULT NULL,
                `model` VARCHAR(100) DEFAULT NULL,
                `images_ids` TEXT DEFAULT NULL,
                `tips_amount` VARCHAR(100) DEFAULT NULL,
                `latitude` varchar(50) DEFAULT NULL,
                `longitude` varchar(50) DEFAULT NULL,
                `full_address` TEXT DEFAULT NULL,
                `address` LONGTEXT DEFAULT NULL,
                `device_status` varchar(50) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
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
