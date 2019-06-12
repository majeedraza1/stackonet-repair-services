<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Integrations\IpStack;
use Stackonet\Supports\Utils;

defined( 'ABSPATH' ) || exit;

class CheckoutAnalysis extends DatabaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'stackonet_checkout_analysis';

	/**
	 * @var string
	 */
	protected $cache_group = 'stackonet_checkout_analysis';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'                        => 0,
		'ip_address'                => null,
		'postal_code'               => null,
		'city'                      => null,
		'user_info'                 => null,
		'device'                    => null,
		'device_model'              => null,
		'device_color'              => null,
		'zip_code'                  => null,
		'unsupported_zip_code'      => null,
		'unsupported_zip_thank_you' => null,
		'screen_cracked'            => null,
		'device_issue'              => null,
		'requested_date_time'       => null,
		'user_address'              => null,
		'user_details'              => null,
		'terms_and_conditions'      => null,
		'thank_you'                 => null,
		'extra_information'         => null,
		'created_by'                => 0,
		'created_at'                => null,
		'updated_at'                => null,
		'deleted_at'                => null,
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
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
	];

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
	 * Create data
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function create( array $data ) {
		$data['ip_address'] = Utils::get_remote_ip();

		if ( '127.0.0.1' != $data['ip_address'] ) {
			$api_key = Settings::get_ipdata_api_key();
			if ( ! empty( $api_key ) ) {
				$ipData = new IpStack( $api_key );
				$ipData->set_ip_address( $data['ip_address'] );
				$ip_data = $ipData->get_city_and_postal_code();

				$data['postal_code'] = ! empty( $ip_data['postal_code'] ) ? sanitize_text_field( $ip_data['postal_code'] ) : null;
				$data['city']        = ! empty( $ip_data['city'] ) ? sanitize_text_field( $ip_data['city'] ) : null;
			}
		}

		return parent::create( $data );
	}

	/**
	 * Check if column is valid
	 *
	 * @param string $column_name
	 *
	 * @return bool
	 */
	public function is_valid_column( $column_name ) {
		return in_array( $column_name, array_keys( $this->default_data ) );
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$counts = wp_cache_get( 'phones_count', $this->cache_group );
		if ( false === $counts ) {
			$query = "SELECT COUNT(*) AS num_entries FROM {$table} WHERE deleted_at IS NULL";
			// $query   .= " GROUP BY status";
			$result = $wpdb->get_row( $query, ARRAY_A );
			$counts = isset( $result['num_entries'] ) ? intval( $result['num_entries'] ) : 0;

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
                `ip_address` varchar(100) DEFAULT NULL,
                `postal_code` varchar(20) DEFAULT NULL,
                `city` varchar(100) DEFAULT NULL,
                `user_info` datetime DEFAULT NULL,
                `device` datetime DEFAULT NULL,
                `device_model` datetime DEFAULT NULL,
                `device_color` datetime DEFAULT NULL,
                `zip_code` datetime DEFAULT NULL,
                `unsupported_zip_code` datetime DEFAULT NULL,
                `unsupported_zip_thank_you` datetime DEFAULT NULL,
                `screen_cracked` datetime DEFAULT NULL,
                `device_issue` datetime DEFAULT NULL,
                `requested_date_time` datetime DEFAULT NULL,
                `user_address` datetime DEFAULT NULL,
                `user_details` datetime DEFAULT NULL,
                `terms_and_conditions` datetime DEFAULT NULL,
                `thank_you` datetime DEFAULT NULL,
                `extra_information` longtext DEFAULT NULL,
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
		$version    = get_option( 'stackonet_checkout_analysis_table_version' );
		$version    = ! empty( $version ) ? $version : '1.0.0';

		if ( version_compare( $version, '1.0.2', '<' ) ) {
			$row = $wpdb->get_row( "SELECT * FROM {$table_name}", ARRAY_A );
			if ( ! isset( $row['user_info'] ) ) {
				$wpdb->query( "ALTER TABLE {$table_name} ADD `user_info` datetime NULL DEFAULT NULL AFTER `city`" );
			}

			update_option( 'stackonet_checkout_analysis_table_version', '1.0.2' );
		}
	}
}
