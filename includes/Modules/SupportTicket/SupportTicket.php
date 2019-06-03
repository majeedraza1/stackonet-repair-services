<?php

namespace Stackonet\Modules\SupportTicket;

use DateTime;
use Exception;
use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Supports\Utils;
use WC_Order;
use WP_Post;
use WP_Term;
use WP_User;

defined( 'ABSPATH' ) or exit;

class SupportTicket extends DatabaseModel {

	/**
	 * Post type name
	 *
	 * @var string
	 */
	protected $table = 'wpsc_ticket';

	/**
	 * @var string
	 */
	protected $meta_table = 'wpsc_ticketmeta';

	/**
	 * @var string
	 */
	protected $post_type = 'wpsc_ticket_thread';

	/**
	 * @var string
	 */
	protected $cache_group = 'support_ticket';

	/**
	 * Object data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $created_at = 'date_created';

	/**
	 * @var string
	 */
	protected $updated_at = 'date_updated';

	/**
	 * @var string
	 */
	protected $created_by = 'agent_created';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'               => 0,
		'ticket_status'    => 0,
		'customer_name'    => '',
		'customer_email'   => '',
		'customer_phone'   => '',
		'city'             => '',
		'ticket_subject'   => '',
		'user_type'        => '',
		'ticket_category'  => '',
		'ticket_priority'  => '',
		'date_created'     => '',
		'date_updated'     => '',
		'ip_address'       => '',
		'agent_created'    => 0,
		'ticket_auth_code' => '',
		'active'           => 1,
	];

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_metadata = [
		'ticket_id'      => 0,
		'thread_type'    => '',
		'customer_name'  => '',
		'customer_email' => '',
		'attachments'    => [],
	];

	/**
	 * @var array
	 */
	protected $valid_thread_types = [ 'report', 'log', 'reply' ];

	/**
	 * @var bool
	 */
	protected $assigned_agent_read = false;

	/**
	 * @var array
	 */
	protected $assigned_agents_ids = [];

	/**
	 * @var array
	 */
	protected $assigned_agents = [];

	/**
	 * Array representation of the class
	 *
	 * @return array
	 * @throws Exception
	 */
	public function to_array() {
		$data                       = parent::to_array();
		$data['customer_url']       = get_avatar_url( $this->get( 'customer_email' ) );
		$data['status']             = $this->get_ticket_status();
		$data['category']           = $this->get_ticket_category();
		$data['priority']           = $this->get_ticket_priority();
		$data['created_by']         = $this->get_agent_created();
		$data['assigned_agents']    = $this->get_assigned_agents();
		$data['updated']            = $this->update_at();
		$data['updated_human_time'] = $this->updated_human_time();

		return $data;
	}

	/**
	 * @return int
	 */
	public function get_ticket_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get ticket threads
	 *
	 * @return WP_Post[]
	 */
	public function get_ticket_threads() {
		return ( new TicketThread() )->find_by_ticket_id( $this->get_ticket_id() );
	}

	/**
	 * @throws Exception
	 */
	public function update_at() {
		$date_updated = $this->get( 'date_updated' );
		$dateTime     = new DateTime( $date_updated );

		return $dateTime->format( get_option( 'date_format' ) );
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function updated_human_time() {
		$date_updated = $this->get( 'date_updated' );
		$dateTime     = new DateTime( $date_updated );

		return human_time_diff( $dateTime->getTimestamp() ) . ' ago';
	}

	/**
	 * @return string
	 */
	public function get_agent_created() {
		$agent_created = $this->get( 'agent_created' );

		if ( is_numeric( $agent_created ) ) {
			$user = get_user_by( 'id', $agent_created );
			if ( $user instanceof WP_User ) {
				return $user->display_name;
			}
		}

		return 'None';
	}

	/**
	 * Get ticket status
	 *
	 * @return array
	 */
	public function get_ticket_status() {
		$ticket_status = $this->get( 'ticket_status' );
		$terms         = get_term_by( 'id', $ticket_status, 'wpsc_statuses' );

		if ( $terms instanceof WP_Term ) {
			return $terms->to_array();
		}

		return [];
	}

	/**
	 * Get ticket category
	 *
	 * @return array
	 */
	public function get_ticket_category() {
		$ticket_status = $this->get( 'ticket_category' );
		$terms         = get_term_by( 'id', $ticket_status, 'wpsc_categories' );
		if ( $terms instanceof WP_Term ) {
			return $terms->to_array();
		}

		return [];
	}

	/**
	 * Get ticket priority
	 *
	 * @return array
	 */
	public function get_ticket_priority() {
		$ticket_status = $this->get( 'ticket_priority' );
		$terms         = get_term_by( 'id', $ticket_status, 'wpsc_priorities' );

		if ( $terms instanceof WP_Term ) {
			return $terms->to_array();
		}

		return [];
	}

	/**
	 * Get assigned agents
	 *
	 * @return array
	 */
	public function get_assigned_agents() {
		$ids = $this->get_assigned_agents_ids();
		if ( ! count( $ids ) ) {
			return [];
		}

		if ( empty( $this->assigned_agents ) ) {
			foreach ( $ids as $id ) {
				$user = get_user_by( 'id', $id );
				if ( ! $user instanceof WP_User ) {
					continue;
				}

				$this->assigned_agents[] = [
					'id'           => $user->ID,
					'email'        => $user->user_email,
					'display_name' => $user->display_name,
					'avatar_url'   => get_avatar_url( $user->user_email ),
				];
			}
		}

		return $this->assigned_agents;
	}

	/**
	 * Get assigned agents ids
	 *
	 * @return array
	 */
	public function get_assigned_agents_ids() {
		if ( ! $this->assigned_agent_read ) {
			$ticket_id = $this->get( 'id' );
			$terms_ids = $this->get_metadata( $ticket_id, 'assigned_agent' );
			if ( empty( $terms_ids ) ) {
				return [];
			}

			foreach ( $terms_ids as $terms_id ) {
				$user_id                     = get_term_meta( $terms_id, 'user_id', true );
				$this->assigned_agents_ids[] = is_numeric( $user_id ) ? intval( $user_id ) : 0;
			}

			$this->assigned_agent_read = true;
		}

		return $this->assigned_agents_ids;
	}

	/**
	 * Create support ticket
	 *
	 * @param array $data
	 * @param string $content
	 * @param string $thread_type
	 *
	 * @return int
	 * @throws Exception
	 */
	public function create_support_ticket( array $data, $content = '', $thread_type = 'report' ) {
		$data = wp_parse_args( $data, [
			'ticket_subject'   => '',
			'customer_name'    => '',
			'customer_email'   => '',
			'user_type'        => get_current_user_id() ? 'user' : 'guest',
			'ticket_status'    => get_option( 'wpsc_default_ticket_status' ),
			'ticket_category'  => get_option( 'wpsc_default_ticket_category' ),
			'ticket_priority'  => get_option( 'wpsc_default_ticket_priority' ),
			'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'agent_created'    => 0,
			'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
			'active'           => 1
		] );

		$ticket_id = $this->create( $data );

		$this->update_metadata( $ticket_id, 'assigned_agent', '0' );

		$this->add_ticket_info( $ticket_id, [
			'thread_type'    => $thread_type,
			'customer_name'  => $data['customer_name'],
			'customer_email' => $data['customer_email'],
			'post_content'   => $content,
			'agent_created'  => $data['agent_created'],
		] );

		return $ticket_id;
	}

	/**
	 * @param int $ticket_id
	 * @param array $data
	 * @param array $attachments
	 */
	public function add_ticket_info( $ticket_id, array $data, $attachments = [] ) {
		$data = wp_parse_args( $data, [
			'thread_type'    => 'report',
			'customer_name'  => '',
			'customer_email' => '',
			'post_content'   => '',
			'agent_created'  => 0,
		] );

		$post_id = wp_insert_post( [
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $data['agent_created'],
			'post_content'   => $data['post_content'],
		] );

		if ( $post_id ) {
			update_post_meta( $post_id, 'ticket_id', $ticket_id );
			update_post_meta( $post_id, 'thread_type', $data['thread_type'] );
			update_post_meta( $post_id, 'customer_name', $data['customer_name'] );
			update_post_meta( $post_id, 'customer_email', $data['customer_email'] );
			update_post_meta( $post_id, 'attachments', $attachments );
		}
	}

	/**
	 * Get ticket meta
	 *
	 * @param int $ticket_id
	 * @param string $meta_key
	 *
	 * @return array
	 */
	public function get_metadata( $ticket_id, $meta_key ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->meta_table;

		$ticket_meta = array();
		$sql         = $wpdb->prepare( "SELECT meta_value FROM {$table} WHERE ticket_id= %d AND meta_key = %s", $ticket_id, $meta_key );
		$results     = $wpdb->get_results( $sql );
		if ( $results ) {
			foreach ( $results as $result ) {
				$ticket_meta[] = stripslashes( $result->meta_value );
			}
		}

		return $ticket_meta;
	}

	/**
	 * Update ticket metadata
	 *
	 * @param int $ticket_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 * @param int $meta_id
	 */
	public function update_metadata( $ticket_id, $meta_key, $meta_value, $meta_id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->meta_table;
		$data  = [
			'ticket_id'  => $ticket_id,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
		];
		if ( $meta_id ) {
			$data['id'] = $meta_id;
		}
		$wpdb->insert( $table, $data );
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
		$order         = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';
		$ticket_status = isset( $args['ticket_status'] ) ? $args['ticket_status'] : 'all';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
			$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
		}

		if ( is_numeric( $ticket_status ) ) {
			$query .= $wpdb->prepare( " AND ticket_status = %d", intval( $ticket_status ) );
		}

		if ( isset( $args['ticket_category'] ) && is_numeric( $args['ticket_category'] ) ) {
			$query .= $wpdb->prepare( " AND ticket_category = %d", intval( $args['ticket_category'] ) );
		}

		if ( isset( $args['ticket_priority'] ) && is_numeric( $args['ticket_priority'] ) ) {
			$query .= $wpdb->prepare( " AND ticket_priority = %d", intval( $args['ticket_priority'] ) );
		}

		if ( isset( $args['city'] ) && ! empty( $args['city'] ) && $args['city'] !== 'all' ) {
			$query .= $wpdb->prepare( " AND city = %s", $args['city'] );
		}

		if ( 'trash' == $ticket_status ) {
			$query .= " AND active = 0";
		} else {
			$query .= " AND active = 1";
		}

		$query   .= " ORDER BY {$orderby} {$order}";
		$query   .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		$data = [];
		foreach ( $results as $result ) {
			$data[] = new self( $result );
		}

		return $data;
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
		$table         = $wpdb->prefix . $this->table;
		$string        = isset( $args['search'] ) ? esc_sql( $args['search'] ) : '';
		$fields        = empty( $fields ) ? array_keys( $this->default_data ) : $fields;
		$ticket_status = ! empty( $args['ticket_status'] ) ? $args['ticket_status'] : 'all';

		$cache_key = sprintf( 'support_ticket_search_%s', md5( json_encode( $args ) ) );
		$phones    = wp_cache_get( $cache_key, $this->cache_group );
		if ( false === $phones ) {
			$query = "SELECT * FROM {$table} WHERE 1 = 1";

			if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
				$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
			}

			if ( 'trash' == $ticket_status ) {
				$query .= " AND active = 0";
			} else {
				$query .= " AND active = 1";
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
					$phones[] = new self( $item );
				}
			}
			wp_cache_add( $cache_key, $phones, $this->cache_group );
		}

		return $phones;
	}

	public function find_all_cities() {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT DISTINCT city FROM {$table};";

		$results = $wpdb->get_results( $query, ARRAY_A );

		$cities = [];
		if ( is_array( $results ) && count( $results ) ) {
			$cities = wp_list_pluck( $results, 'city' );
			$cities = array_filter( array_values( $cities ) );
		}

		return $cities;
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
	 * Update support ticket agents
	 *
	 * @param array $agents_ids
	 */
	public function update_agent( array $agents_ids ) {
		global $wpdb;
		$table     = $wpdb->prefix . $this->meta_table;
		$meta_key  = 'assigned_agent';
		$ticket_id = $this->get( 'id' );

		$sql     = $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_id= %d AND meta_key = %s", $ticket_id, $meta_key );
		$results = $wpdb->get_results( $sql );
		if ( count( $results ) ) {
			foreach ( $results as $result ) {
				$wpdb->delete( $table, [ 'id' => $result->id ], '%d' );
			}
		}

		$agents = SupportAgent::get_all();
		foreach ( $agents as $agent ) {
			foreach ( $agents_ids as $agents_id ) {
				if ( $agent->get_user()->ID == $agents_id ) {
					$this->update_metadata( $ticket_id, $meta_key, $agent->get( 'term_id' ) );
				}
			}
		}
	}

	/**
	 * Delete data
	 *
	 * @param int $thread_id
	 *
	 * @return bool
	 */
	public function delete_thread( $thread_id = 0 ) {
		return wp_delete_post( $thread_id ) instanceof WP_Post;
	}

	/**
	 * Send an item to trash
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function trash( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$query = $wpdb->update( $table, [ 'active' => 0 ], [ $this->primaryKey => $id ]
		);

		return ( false !== $query );
	}

	/**
	 * Restore an item from trash
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function restore( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$query = $wpdb->update( $table, [ 'active' => 1 ], [ $this->primaryKey => $id ] );

		return ( false !== $query );
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	public function count_records() {
		global $wpdb;
		$table    = $wpdb->prefix . $this->table;
		$statuses = $this->get_ticket_statuses_terms();
		$counts   = wp_cache_get( 'support_tickets_count', $this->cache_group );
		if ( false === $counts ) {
			$query   = "SELECT ticket_status, COUNT( * ) AS num_entries FROM {$table} WHERE active = 1";
			$query   .= " GROUP BY ticket_status";
			$results = $wpdb->get_results( $query, ARRAY_A );

			foreach ( $statuses as $status ) {
				$counts[ $status->term_id ] = 0;
			}

			foreach ( $results as $row ) {
				$counts[ $row['ticket_status'] ] = intval( $row['num_entries'] );
			}
			$counts['all']   = array_sum( $counts );
			$counts['trash'] = $this->count_trash_records();

			wp_cache_set( 'support_tickets_count', $counts, $this->cache_group );
		}

		return $counts;
	}

	/**
	 * Cont trash records
	 *
	 * @return int
	 */
	public function count_trash_records() {
		global $wpdb;
		$table   = $wpdb->prefix . $this->table;
		$query   = "SELECT COUNT( * ) AS num_entries FROM {$table} WHERE active = 0";
		$results = $wpdb->get_row( $query, ARRAY_A );

		return intval( $results['num_entries'] );
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_ticket_statuses_terms() {
		$terms = get_terms( array(
			'taxonomy'   => 'wpsc_statuses',
			'hide_empty' => false,
		) );

		return $terms;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_categories_terms() {
		$terms = get_terms( array(
			'taxonomy'   => 'wpsc_categories',
			'hide_empty' => false,
		) );

		return $terms;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_priorities_terms() {
		$terms = get_terms( array(
			'taxonomy'   => 'wpsc_priorities',
			'hide_empty' => false,
		) );

		return $terms;
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

		$tables = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			ticket_status integer,
			customer_name TINYTEXT NULL DEFAULT NULL,
			customer_email TINYTEXT NULL DEFAULT NULL,
			customer_phone varchar(20) NULL DEFAULT NULL,
			ticket_subject varchar(200) NULL DEFAULT NULL,
			city varchar(100) NULL DEFAULT NULL,
			user_type varchar(30) NULL DEFAULT NULL,
			ticket_category integer,
			ticket_priority integer,
			date_created datetime,
			date_updated datetime,
			ip_address VARCHAR(30) NULL DEFAULT NULL,
			agent_created INT NULL DEFAULT '0',
			ticket_auth_code LONGTEXT NULL DEFAULT NULL,
			active int(11) DEFAULT 1,
			PRIMARY KEY  (id)
		) $collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $tables );

		$this->add_table_columns();
	}

	/**
	 * Add new columns to table
	 */
	public function add_table_columns() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$version    = get_option( 'stackonet_support_ticket_table_version' );
		$version    = ! empty( $version ) ? $version : '1.0.0';

		if ( version_compare( $version, '1.0.1', '<' ) ) {
			$row = $wpdb->get_row( "SELECT * FROM {$table_name}", ARRAY_A );

			if ( ! isset( $row['customer_phone'] ) ) {
				$wpdb->query( "ALTER TABLE {$table_name} ADD `customer_phone` VARCHAR(20) NULL DEFAULT NULL AFTER `customer_email`" );
			}

			if ( ! isset( $row['city'] ) ) {
				$wpdb->query( "ALTER TABLE {$table_name} ADD `city` VARCHAR(100) NULL DEFAULT NULL AFTER `ticket_subject`" );
			}

			update_option( 'stackonet_support_ticket_table_version', '1.0.1' );
		}
	}
}
