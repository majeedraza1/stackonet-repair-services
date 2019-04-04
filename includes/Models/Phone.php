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
		'status'        => 'processing',
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
	 * Get available status
	 *
	 * @return array
	 */
	public static function available_status() {
		return [
			'processing'     => __( 'Processing', 'stackonet-repair-services' ),
			'arriving-soon'  => __( 'Arriving Soon', 'stackonet-repair-services' ),
			'picked-off'     => __( 'Picked off', 'stackonet-repair-services' ),
			'not-picked-off' => __( 'Not Picked off', 'stackonet-repair-services' ),
			'repairing'      => __( 'Repairing', 'stackonet-repair-services' ),
			'not-repaired'   => __( 'Not Repaired', 'stackonet-repair-services' ),
			'delivered'      => __( 'Delivered', 'stackonet-repair-services' ),
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
		$items   = [];
		$results = parent::find( $args );
		if ( $results ) {
			foreach ( $results as $result ) {
				$result['issues'] = maybe_unserialize( $result['issues'] );
				$items[]          = new self( $result );
			}
		}

		return $items;
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
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$status = self::available_status();
		$counts = wp_cache_get( 'phones_count', $this->cache_group );
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
			wp_cache_set( 'phones_count', $counts, $this->cache_group );
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
