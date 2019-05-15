<?php

namespace Stackonet\Models;

use Stackonet\Abstracts\DatabaseModel;
use WP_User;

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
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'               => 0,
		'gadget'           => '',
		'brand'            => '',
		'device'           => '',
		'device_model'     => '',
		'device_issues'    => '',
		'appointment_date' => '',
		'appointment_time' => '',
		'email'            => '',
		'phone'            => '',
		'store_name'       => '',
		'full_address'     => '',
		'address'          => '',
		'images_ids'       => '',
		'note'             => '',
		'status'           => '',
		'created_by'       => 0,
		'created_at'       => '',
		'updated_at'       => '',
		'deleted_at'       => '',
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
		'%s'
	];

	/**
	 * @var bool|WP_User
	 */
	private $author;

	/**
	 * @var array
	 */
	private $images = [];

	/**
	 * Get available status
	 *
	 * @return array
	 */
	public static function available_status() {
		return [
			'unread' => __( 'Unread', 'stackonet-repair-services' ),
			'read'   => __( 'Read', 'stackonet-repair-services' ),
		];
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$data           = parent::to_array();
		$data['id']     = intval( $data['id'] );
		$data['author'] = [
			'display_name' => $this->get_author_display_name(),
		];
		$data['images'] = $this->get_images();

		return $data;
	}

	/**
	 * Get survey images
	 *
	 * @return array
	 */
	public function get_images() {
		if ( empty( $this->images ) ) {
			$images_ids = $this->get( 'images_ids' );
			if ( is_array( $images_ids ) ) {
				$images = [];
				foreach ( $images_ids as $image_id ) {
					$images[] = $this->get_image_by_id( $image_id );
				}
				$this->images = array_values( array_filter( $images ) );
			}
		}

		return $this->images;
	}

	/**
	 * Get image by image id
	 *
	 * @param int $image_id
	 *
	 * @return array
	 */
	public function get_image_by_id( $image_id ) {
		$title          = get_the_title( $image_id );
		$token          = get_post_meta( $image_id, '_delete_token', true );
		$attachment_url = wp_get_attachment_url( $image_id );
		$image          = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$full_image     = wp_get_attachment_image_src( $image_id, 'full' );

		if ( ! filter_var( $full_image[0], FILTER_VALIDATE_URL ) ) {
			return [];
		}

		$data = [
			'image_id'       => $image_id,
			'title'          => $title,
			'token'          => $token,
			'attachment_url' => $attachment_url,
			'thumbnail'      => [ 'src' => $image[0], 'width' => $image[1], 'height' => $image[2], ],
			'full'           => [ 'src' => $full_image[0], 'width' => $full_image[1], 'height' => $full_image[2], ],
		];

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
	 * Get author display name
	 */
	public function get_author_display_name() {
		return $this->get_author()->display_name;
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
		if ( is_string( $status ) && in_array( $status, $valid_status ) ) {
			$query .= $wpdb->prepare( " AND status = %s", $status );
		}

		if ( is_array( $status ) ) {
			$query .= " AND (";
			foreach ( $status as $index => $m_stage ) {
				if ( $index !== 0 ) {
					$query .= " OR";
				}
				$query .= $wpdb->prepare( " status = %s", $m_stage );
			}
			$query .= ")";
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
				$result['address']       = maybe_unserialize( $result['address'] );
				$result['images_ids']    = maybe_unserialize( $result['images_ids'] );
				$result['device_issues'] = maybe_unserialize( $result['device_issues'] );
				$items[]                 = new self( $result );
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

		$cache_key = sprintf( 'spot_appointment_search_%s', md5( json_encode( $args ) ) );
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

			if ( isset( $args['appointment_id__in'] ) && is_array( $args['appointment_id__in'] ) ) {
				$ids   = array_map( function ( $id ) {
					$id = intval( $id );

					return $id > 0 ? $id : 0;
				}, $args['appointment_id__in'] );
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
					$item['address']       = maybe_unserialize( $item['address'] );
					$item['images_ids']    = maybe_unserialize( $item['images_ids'] );
					$item['device_issues'] = maybe_unserialize( $item['device_issues'] );
					$phones[]              = new self( $item );
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
			$item['address']       = maybe_unserialize( $item['address'] );
			$item['images_ids']    = maybe_unserialize( $item['images_ids'] );
			$item['device_issues'] = maybe_unserialize( $item['device_issues'] );

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
                `gadget` VARCHAR(100) DEFAULT NULL,
                `brand` VARCHAR(100) DEFAULT NULL,
                `device` VARCHAR(100) DEFAULT NULL,
                `device_model` VARCHAR(100) DEFAULT NULL,
                `device_issues` LONGTEXT DEFAULT NULL,
                `appointment_date` VARCHAR(100) DEFAULT NULL,
                `appointment_time` VARCHAR(100) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `store_name` varchar(100) DEFAULT NULL,
                `full_address` TEXT DEFAULT NULL,
                `address` LONGTEXT DEFAULT NULL,
                `images_ids` TEXT DEFAULT NULL,
                `note` TEXT DEFAULT NULL,
                `status` varchar(50) DEFAULT NULL,
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
