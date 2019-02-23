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
}
