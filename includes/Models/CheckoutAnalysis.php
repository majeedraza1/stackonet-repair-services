<?php

namespace Stackonet\Models;

use DateTime;
use Exception;
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
		'support_ticket_id'         => 0,
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
		'%d',
		'%s',
		'%s',
		'%s',
		'%s',
	];

	/**
	 * Get extra information
	 *
	 * @return array
	 */
	public function extra_information() {
		return is_array( $this->data['extra_information'] ) ? $this->data['extra_information'] : [];
	}

	/**
	 * Get first name
	 *
	 * @return mixed|string
	 */
	public function get_first_name() {
		$extra_data = $this->extra_information();

		return isset( $extra_data['user_info']['first_name'] ) ? $extra_data['user_info']['first_name'] : '';
	}

	/**
	 * Get last name
	 *
	 * @return mixed|string
	 */
	public function get_last_name() {
		$extra_data = $this->extra_information();

		return isset( $extra_data['user_info']['last_name'] ) ? $extra_data['user_info']['last_name'] : '';
	}

	/**
	 * Get full name
	 *
	 * @return mixed|string
	 */
	public function get_full_name() {
		$fName = $this->get_first_name();
		$lName = $this->get_last_name();

		if ( ! empty( $fName ) && ! empty( $lName ) ) {
			return sprintf( "%s %s", $fName, $lName );
		}

		if ( ! empty( $lName ) ) {
			return $lName;
		}

		if ( ! empty( $fName ) ) {
			return $fName;
		}

		return '';
	}

	/**
	 * Get first name
	 *
	 * @return mixed|string
	 */
	public function get_phone() {
		$extra_data = $this->extra_information();

		return isset( $extra_data['user_info']['phone'] ) ? $extra_data['user_info']['phone'] : '';
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function get_rest_response_data() {
		$_data = $this->data;
		$data  = [
			'id'          => intval( $this->get( 'id' ) ),
			'ip_address'  => $this->get( 'ip_address' ),
			'city'        => $this->get( 'city' ),
			'postal_code' => $this->get( 'postal_code' ),
		];

		$default_data = [
			'user_info',
			'device',
			'device_model',
			'device_color',
			'zip_code',
			'screen_cracked',
			'device_issue',
			'requested_date_time',
			'user_address',
			'user_details',
			'terms_and_conditions',
			'thank_you',
		];

		$active_item = 0;

		foreach ( $_data as $key => $value ) {
			if ( ! in_array( $key, $default_data ) ) {
				continue;
			}

			switch ( $key ) {
				case 'user_info';
					$label = 'User Info';
					break;
				case 'device_model';
					$label = 'Model';
					break;
				case 'device_color';
					$label = 'Color';
					break;
				case 'zip_code';
					$label = 'ZIP';
					break;
				case 'screen_cracked';
					$label = 'Screen Crack';
					break;
				case 'device_issue';
					$label = 'Issues';
					break;
				case 'requested_date_time';
					$label = 'Time';
					break;
				case 'user_address';
					$label = 'Address';
					break;
				case 'user_details';
					$label = 'User Details';
					break;
				case 'terms_and_conditions';
					$label = 'Terms';
					break;
				case 'thank_you';
					$label = 'Complete';
					break;
				default:
					$label = str_replace( '_', ' ', $key );
					$label = str_replace( 'and', '&', $label );
					$label = ucwords( $label );
					break;
			}

			$active = ! empty( $value );
			if ( $active ) {
				$active_item += 1;
			}

			$dateTime = new DateTime( $value );

			$data['steps'][] = [
				'label'    => $label,
				'datetime' => $value,
				'date'     => $dateTime->format( 'M d, Y' ),
				'time'     => $dateTime->format( 'H:i:s' ),
				'active'   => $active,
				'value'    => isset( $_data[ $key ] ) ? $_data[ $key ] : null,
			];
		}

		$data['steps_count']      = count( $data['steps'] );
		$data['steps_completed']  = $active_item;
		$data['steps_percentage'] = round( ( $active_item / $data['steps_count'] ) * 100 );

		return $data;
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
                `support_ticket_id` bigint(20) DEFAULT NULL,
                `created_by` bigint(20) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}

	/**
	 * @return array|object|null
	 */
	public static function needToAddSupport() {
		global $wpdb;
		$self       = new self();
		$table_name = $wpdb->prefix . $self->table;

		$sql     = "SELECT * FROM {$table_name} WHERE 1 = 1";
		$sql     .= " AND ( thank_you IS NULL OR thank_you = '' )";
		$sql     .= " AND ( support_ticket_id IS NULL OR support_ticket_id = '' OR support_ticket_id = 0 )";
		$sql     .= " AND user_info IS NOT NULL";
		$sql     .= " AND updated_at < DATE_SUB( NOW(), INTERVAL 10 MINUTE )";
		$results = $wpdb->get_results( $sql, ARRAY_A );

		$items = [];
		foreach ( $results as $result ) {
			$items[] = new self( $result );
		}

		return $items;
	}
}
