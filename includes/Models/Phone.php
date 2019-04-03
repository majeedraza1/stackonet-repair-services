<?php


namespace Stackonet\Models;


use Stackonet\Abstracts\DatabaseModel;

class Phone extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_phones';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'            => 0,
		'asset_number'  => '',
		'brand_name'    => '',
		'model'         => '',
		'color'         => '',
		'imei_number'   => '',
		'broken_screen' => 'no',
		'issues'        => '',
		'extra_info'    => '',
		'status'        => '',
		'created_by'    => 0,
		'created_at'    => '',
		'updated_at'    => '',
		'deleted_at'    => '',
	];

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ];

	/**
	 * @var string
	 */
	protected $cache_group = 'phone_repairs_asap';

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
			$item['issues'] = maybe_unserialize( $item['issues'] );

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
                `asset_number` varchar(50) DEFAULT NULL,
                `brand_name` varchar(100) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `color` varchar(255) DEFAULT NULL,
                `imei_number` varchar(255) DEFAULT NULL,
                `broken_screen` varchar(10) DEFAULT NULL,
                `issues` LONGTEXT DEFAULT NULL,
                `extra_info` LONGTEXT DEFAULT NULL,
                `status` varchar(255) DEFAULT NULL,
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
