<?php

namespace Stackonet\Models;


class UnsupportedArea implements \JsonSerializable {

	const TABLE_NAME = 'unsupported_areas';

	/**
	 * Table name
	 *
	 * @var string
	 */
	private static $table;

	/**
	 * Default Data
	 *
	 * @var array
	 */
	private static $default = array(
		'id'           => 0,
		'user_id'      => 0,
		'user_ip'      => '127.0.0.1',
		'user_agent'   => null,
		'email'        => null,
		'zip_code'     => null,
		'device_title' => null,
		'device_model' => null,
		'device_color' => null,
		'status'       => 'unread',
		'created_at'   => null,
		'deleted_at'   => null,
	);

	/**
	 * Message data format
	 *
	 * @var array
	 */
	private static $format = [
		'%d',
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
	];

	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * @var array
	 */
	private $entry = array();

	/**
	 * UnsupportedArea constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = array() ) {
		global $wpdb;
		$this->db    = $wpdb;
		self::$table = $wpdb->prefix . self::TABLE_NAME;

		if ( ! empty( $data ) ) {
			$this->entry = $data;
		}
	}

	/**
	 * Does this entry have a given key?
	 *
	 * @param string $key The data key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->entry[ $key ] );
	}

	/**
	 * Set collection item
	 *
	 * @param string $key The data key
	 * @param mixed $value The data value
	 */
	public function set( $key, $value ) {
		if ( is_null( $key ) ) {
			$this->entry[] = $value;
		} else {
			$this->entry[ $key ] = $value;
		}
	}

	/**
	 * Get entry item for key
	 *
	 * @param string $key The data key
	 * @param mixed $default The default value to return if data key does not exist
	 *
	 * @return mixed The key's value, or the default value
	 */
	public function get( $key, $default = null ) {
		return $this->has( $key ) ? $this->entry[ $key ] : $default;
	}

	/**
	 * Remove item from collection
	 *
	 * @param string $key The data key
	 */
	public function remove( $key ) {
		if ( $this->has( $key ) ) {
			unset( $this->entry[ $key ] );
		}
	}

	/**
	 * Get array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'id'           => $this->get( 'id' ),
			'user_id'      => $this->get( 'user_id' ),
			'user_ip'      => $this->get( 'user_ip' ),
			'user_agent'   => $this->get( 'user_agent' ),
			'email'        => $this->get( 'email' ),
			'zip_code'     => $this->get( 'zip_code' ),
			'device_title' => $this->get( 'device_title' ),
			'device_model' => $this->get( 'device_model' ),
			'device_color' => $this->get( 'device_color' ),
			'status'       => $this->get( 'status', 'unread' ),
			'created_at'   => $this->get( 'created_at' ),
			'deleted_at'   => $this->get( 'deleted_at' ),
		);
	}

	/**
	 * Find entries
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = array() ) {
		$table_name = self::$table;

		$orderby  = isset( $args['orderby'] ) ? $args['orderby'] : 'created_at';
		$order    = isset( $args['order'] ) ? $args['order'] : 'desc';
		$offset   = isset( $args['offset'] ) ? intval( $args['offset'] ) : 0;
		$per_page = isset( $args['per_page'] ) ? intval( $args['per_page'] ) : 100;
		$sql      = "SELECT * FROM {$table_name}";
		$sql      .= " ORDER BY {$orderby} {$order}";
		$sql      .= " LIMIT $per_page OFFSET $offset";
		$items    = $this->db->get_results( $sql, ARRAY_A );
		if ( ! $items ) {
			return array();
		}

		$data = [];
		foreach ( $items as $item ) {
			$data[] = new self( $item );
		}

		return $data;
	}

	public function all( array $args = array() ) {
		return $this->find();
	}

	/**
	 * Get entry by entry ID
	 *
	 * @param int $id
	 *
	 * @return self|false
	 */
	public function findById( $id ) {
		$table_name = self::$table;

		$items = $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( ! $items ) {
			return false;
		}

		return new self( $items );
	}

	/**
	 * Insert a row into a entries table with meta values.
	 *
	 * @param array $data Form data in $key => 'value' format
	 * array( $key1 => 'value 1', $key2 => 'value 2' )
	 *
	 * @return int last insert id
	 */
	public function insert( array $data ) {
		$current_time = current_time( 'mysql' );
		$data         = wp_parse_args( $data, self::$default );

		$data['user_id']    = get_current_user_id();
		$data['user_ip']    = self::get_remote_ip();
		$data['user_agent'] = self::get_user_agent();
		$data['created_at'] = $current_time;

		unset( $data['id'] );

		$this->db->insert( self::$table, $data );
		$insert_id = $this->db->insert_id;

		return $insert_id;
	}

	public function update( array $data, $id = 0 ) {
		$now = current_time( 'mysql' );

		if ( ! $id ) {
			$id = isset( $data['id'] ) ? intval( $data['id'] ) : 0;
		}

		$entry = $this->findById( $id );
		if ( ! $entry instanceof self ) {
			return false;
		}

		$_data = [];
		foreach ( self::$default as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $entry->get( $key );
		}

		$_data['id']         = $id;
		$_data['created_at'] = $entry->get( 'created_at' );

		if ( $this->db->update( self::$table, $data, [ 'id' => $id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Create table
	 */
	public function create_table() {
		global $wpdb;

		$table_name = self::$table;
		$collate    = $wpdb->get_charset_collate();

		$table_schema = "CREATE TABLE IF NOT EXISTS {$table_name} (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `user_ip` varchar(45) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `zip_code` varchar(255) DEFAULT NULL,
                `device_title` varchar(255) DEFAULT NULL,
                `device_model` varchar(255) DEFAULT NULL,
                `device_color` varchar(255) DEFAULT NULL,
                `status` varchar(20) DEFAULT 'unread',
                `created_at` datetime DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table_schema );
	}

	/**
	 * Get user IP address
	 *
	 * @return string
	 */
	public static function get_remote_ip() {
		$server_ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return $_SERVER[ $key ];
			}
		}

		// Fallback local ip.
		return '127.0.0.1';
	}

	/**
	 * Get user browser name
	 *
	 * @return string
	 */
	public static function get_user_agent() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
		}

		return '';
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
