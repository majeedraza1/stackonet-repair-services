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
		$data                    = parent::to_array();
		$data['customer_url']    = get_avatar_url( $this->get( 'customer_email' ) );
		$data['status']          = $this->get_ticket_status();
		$data['category']        = $this->get_ticket_category();
		$data['priority']        = $this->get_ticket_priority();
		$data['created_by']      = $this->get_agent_created();
		$data['assigned_agents'] = $this->get_assigned_agents();
		$data['updated']         = $this->update_at();

		return $data;
	}

	public function get_ticket_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get ticket threads
	 *
	 * @return WP_Post[]
	 */
	public function get_ticket_threads() {
		$args = array(
			'post_type'      => 'wpsc_ticket_thread',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => - 1,
			'meta_query'     => array(
				'relation'   => 'AND',
				array(
					'key'     => 'ticket_id',
					'value'   => $this->get_ticket_id(),
					'compare' => '='
				),
				'meta_query' => array(),
			)
		);

		$_threads = get_posts( $args );
		$threads  = [];

		foreach ( $_threads as $thread ) {
			$ticket_id      = get_post_meta( $thread->ID, 'ticket_id', true );
			$thread_type    = get_post_meta( $thread->ID, 'thread_type', true );
			$customer_name  = get_post_meta( $thread->ID, 'customer_name', true );
			$customer_email = get_post_meta( $thread->ID, 'customer_email', true );
			$_attachments   = get_post_meta( $thread->ID, 'attachments', true );

			$attachments = [];
			if ( is_array( $_attachments ) && count( $_attachments ) ) {
				foreach ( $_attachments as $attachment_id ) {

					$save_file_name = get_term_meta( $attachment_id, 'save_file_name', true );
					$is_image       = (bool) get_term_meta( $attachment_id, 'is_image', true );

					$upload_dir   = wp_upload_dir();
					$file_url     = $upload_dir['baseurl'] . '/wpsc/' . $save_file_name;
					$download_url = $is_image ? $file_url : site_url( '/' ) . '?wpsc_attachment=' . $attachment_id . '&tid=' . $ticket_id . '&tac=' . 0;

					$attachments[] = [
						'filename'       => get_term_meta( $attachment_id, 'filename', true ),
						'active'         => (bool) get_term_meta( $attachment_id, 'active', true ),
						'is_image'       => $is_image,
						'save_file_name' => $save_file_name,
						'time_uploaded'  => get_term_meta( $attachment_id, 'time_uploaded', true ),
						'download_url'   => $download_url,
					];
				}
			}

			$human_time = human_time_diff( strtotime( $thread->post_date_gmt ) );

			$threads[] = [
				'thread_id'           => $thread->ID,
				'thread_content'      => $thread->post_content,
				'thread_date'         => $thread->post_date_gmt,
				'human_time'          => $human_time,
				'thread_type'         => $thread_type,
				'customer_name'       => $customer_name,
				'customer_email'      => $customer_email,
				'customer_avatar_url' => get_avatar_url( $customer_email ),
				'attachments'         => $attachments,
			];
		}

		return $threads;
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

		return $terms->to_array();
	}

	/**
	 * Get ticket category
	 *
	 * @return array
	 */
	public function get_ticket_category() {
		$ticket_status = $this->get( 'ticket_category' );
		$terms         = get_term_by( 'id', $ticket_status, 'wpsc_categories' );

		return $terms->to_array();
	}

	/**
	 * Get ticket priority
	 *
	 * @return array
	 */
	public function get_ticket_priority() {
		$ticket_status = $this->get( 'ticket_priority' );
		$terms         = get_term_by( 'id', $ticket_status, 'wpsc_priorities' );

		return $terms->to_array();
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
			$_agents   = $this->get_metadata( $ticket_id, 'assigned_agent' );
			if ( empty( $_agents ) ) {
				return [];
			}

			foreach ( $_agents as $agent ) {
				$user                        = get_term_meta( $agent );
				$user_id                     = isset( $user['user_id'][0] ) ? intval( $user['user_id'][0] ) : 0;
				$this->assigned_agents_ids[] = $user_id;
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
			'user_type'        => 'guest',
			'ticket_status'    => get_option( 'wpsc_default_ticket_status' ),
			'ticket_category'  => get_option( 'wpsc_default_ticket_category' ),
			'ticket_priority'  => get_option( 'wpsc_default_ticket_priority' ),
			'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'agent_created'    => 0,
			'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
			'active'           => '1'
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
	 * @param WC_Order $order
	 * @param array $data
	 */
	public function order_reschedule_to_support_ticket( WC_Order $order, array $data ) {
		$support_ticket_id = $order->get_meta( '_support_ticket_id' );
		$created_by        = ( $data['created_by'] === 'admin' ) ? 'Admin' : 'Customer';

		ob_start();
		echo "Order has been reschedule by <strong>{$created_by}</strong>. New date and time are<br>";
		echo "Date: <strong>{$data['date']}</strong><br>";
		echo "Time: <strong>{$data['time']}</strong>";
		$post_content = ob_get_clean();

		$this->add_ticket_info( $support_ticket_id, [
			'thread_type'    => 'note',
			'customer_name'  => $order->get_formatted_billing_full_name(),
			'customer_email' => $order->get_billing_email(),
			'post_content'   => $post_content,
			'agent_created'  => 0,
		] );
	}

	/**
	 * Create support ticket from order
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function order_to_support_ticket( WC_Order $order ) {
		$_device_title  = $order->get_meta( '_device_title' );
		$_device_model  = $order->get_meta( '_device_model' );
		$_device_color  = $order->get_meta( '_device_color' );
		$_device_issues = $order->get_meta( '_device_issues' );
		$service_date   = $order->get_meta( '_preferred_service_date' );
		$service_time   = $order->get_meta( '_preferred_service_time_range' );

		$timezone = Utils::get_timezone();
		$dateTime = new DateTime( $service_date, $timezone );
		$date     = $dateTime->format( get_option( 'date_format' ) );

		$ticket_subject = $_device_title . ' ' . $_device_model . ' - ' . implode( ', ', $_device_issues );

		$order_url = add_query_arg( [ 'post' => $order->get_id(), 'action' => 'edit' ], admin_url( 'post.php' ) );

		$address = $order->get_address( 'shipping' );
		unset( $address['first_name'], $address['last_name'], $address['company'] );
		$address = WC()->countries->get_formatted_address( $address, ', ' );

		ob_start();
		?>
		<table>
			<tr>
				<td>Order ID:</td>
				<td><strong>#<?php echo $order->get_id() ?></strong></td>
			</tr>
			<tr>
				<td>Device:</td>
				<td>
					<strong>
						<?php echo $_device_title; ?>
						<?php echo $_device_model; ?> (<?php echo $_device_color; ?> )
					</strong>
				</td>
			</tr>
			<tr>
				<td> Order URL:</td>
				<td><a target="_blank" href="<?php echo $order_url; ?>"><strong><?php echo $order_url; ?></strong></a>
				</td>
			</tr>
			<tr>
				<td> Customer Address:</td>
				<td><a target="_blank"
				       href="<?php echo $order->get_shipping_address_map_url(); ?>"><?php echo $address; ?></a></td>
			</tr>
			<tr>
				<td>Preferred Date & Time:</td>
				<td><strong><?php echo $date . ', ' . $service_time; ?></strong></td>
			</tr>
		</table>
		<hr>
		<p>Issues:</p>
		<table class="table--issues" style="width: 100%;">
			<tr>
				<th>Issue</th>
				<th>Total</th>
				<th>Tax</th>
			</tr>
			<?php
			$fees = $order->get_fees();
			foreach ( $fees as $fee ) {
				echo '<tr>
					<td>' . $fee->get_name() . '</td>
					<td>' . $fee->get_total() . '</td>
					<td>' . $fee->get_total_tax() . '</td>
				</tr>';
			}
			?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Tax:</td>
				<td><strong><?php echo $order->get_total_tax(); ?></strong></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Total:</td>
				<td><strong><?php echo $order->get_total(); ?></strong></td>
			</tr>
		</table>
		<?php
		$post_content = ob_get_clean();

		$ticket_id = $this->create_support_ticket( [
			'ticket_subject'  => $ticket_subject,
			'customer_name'   => $order->get_formatted_billing_full_name(),
			'customer_email'  => $order->get_billing_email(),
			'user_type'       => $order->get_customer_id() ? 'user' : 'guest',
			'ticket_category' => get_option( 'wpsc_default_order_ticket_category' ),
		], $post_content );

		$order->add_meta_data( '_support_ticket_id', $ticket_id );
		$order->save_meta_data();
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
	 * Delete data
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete( $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		return ( false !== $wpdb->delete( $table, [ $this->primaryKey => $id ], $this->primaryKeyType ) );
	}

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
		return;
	}
}
