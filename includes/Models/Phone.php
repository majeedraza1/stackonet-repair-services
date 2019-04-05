<?php


namespace Stackonet\Models;


use Stackonet\Abstracts\DatabaseModel;
use WP_User;

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
	 * Phone author data
	 *
	 * @var array
	 */
	private $author = [];

	public function to_array() {
		$data           = parent::to_array();
		$data['author'] = $this->get_author()->display_name;

		return $data;
	}

	/**
	 * Get author info
	 *
	 * @return WP_User
	 */
	public function get_author() {
		if ( empty( $this->author ) ) {
			$created_by = $this->get( 'created_by' );
			$author     = get_user_by( 'id', $created_by );
			if ( $author instanceof WP_User ) {
				$this->author = $author;
			}
		}

		return $this->author;
	}

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
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primaryKey;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
			$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
		}

		$valid_status = array_keys( self::available_status() );
		$status       = isset( $args['status'] ) ? $args['status'] : 'all';
		if ( in_array( $status, $valid_status ) ) {
			$query .= $wpdb->prepare( " AND status = %s", $status );
		}

		if ( 'trash' == $status ) {
			$query .= " AND deleted_at IS NOT NULL";
		} else {
			$query .= " AND deleted_at IS NULL";
		}

		$query   .= " ORDER BY {$orderby} {$order}";
		$query   .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		$items = [];
		if ( $results ) {
			foreach ( $results as $result ) {
				$result['issues'] = maybe_unserialize( $result['issues'] );
				$items[]          = new self( $result );
			}
		}

		return $items;
	}

	/**
	 * Search phone
	 *
	 * @param array $args
	 * @param array $fields
	 *
	 * @return array
	 */
	public function search( $args, $fields = [] ) {
		global $wpdb;
		$table  = $wpdb->prefix . $this->table;
		$string = isset( $args['search'] ) ? esc_sql( $args['search'] ) : '';
		$fields = empty( $fields ) ? array_keys( $this->default_data ) : $fields;
		$status = ! empty( $args['status'] ) ? $args['status'] : 'all';

		$cache_key = sprintf( 'phones_search_%s', md5( json_encode( $args ) ) );
		$phones    = wp_cache_get( $cache_key, $this->cache_group );
		if ( false === $phones ) {
			$query = "SELECT * FROM {$table} WHERE 1 = 1";

			if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
				$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
			}

			if ( 'trash' == $status ) {
				$query .= " AND deleted_at IS NOT NULL";
			} else {
				$query .= " AND deleted_at IS NULL";
			}

			if ( in_array( $status, array_keys( self::available_status() ) ) ) {
				$query .= $wpdb->prepare( " AND status = %s", $status );
			}

			if ( isset( $args['phone_id__in'] ) && is_array( $args['phone_id__in'] ) ) {
				$ids   = array_map( function ( $id ) {
					$id = intval( $id );

					return $id > 0 ? $id : 0;
				}, $args['phone_id__in'] );
				$query .= " AND id IN(" . implode( ',', array_filter( $ids ) ) . ")";
			}
			$total_fields = count( $fields );
			foreach ( $fields as $index => $field ) {
				if ( 0 === $index ) {
					$query .= " AND ({$field} LIKE '%{$string}%'";
				} elseif ( ( $total_fields - 1 ) === $index ) {
					$query .= " OR {$field} LIKE '%{$string}%')";
				} else {
					$query .= " OR {$field} LIKE '%{$string}%'";
				}
			}
			$items = $wpdb->get_results( $query, ARRAY_A );
			if ( $items ) {
				foreach ( $items as $item ) {
					$item['issues'] = maybe_unserialize( $item['issues'] );
					$phones[]       = new self( $item );
				}
			}
			wp_cache_add( $cache_key, $phones, $this->cache_group );
		}

		return $phones;
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
