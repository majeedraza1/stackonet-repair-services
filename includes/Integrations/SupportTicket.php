<?php

namespace Stackonet\Integrations;

use DateTime;
use Exception;
use Stackonet\Abstracts\DatabaseModel;
use Stackonet\Supports\Utils;
use WC_Order;

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
	 * Object data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

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
	 * @var null
	 */
	public static $instance = null;

	/**
	 * @return SupportTicket
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'wpsc_get_gerneral_settings', [ self::$instance, 'settings' ] );
			add_action( 'wpsc_set_gerneral_settings', [ self::$instance, 'save_settings' ] );
		}

		return self::$instance;
	}

	public function save_settings() {
		$order_ticket_category = isset( $_POST['wpsc_default_order_ticket_category'] ) ? sanitize_text_field( $_POST['wpsc_default_order_ticket_category'] ) : '';
		update_option( 'wpsc_default_order_ticket_category', $order_ticket_category );
		$order_ticket_category = isset( $_POST['wpsc_default_contact_form_ticket_category'] ) ? sanitize_text_field( $_POST['wpsc_default_contact_form_ticket_category'] ) : '';
		update_option( 'wpsc_default_contact_form_ticket_category', $order_ticket_category );
	}

	public function settings() {
		$categories = get_terms( [
			'taxonomy'   => 'wpsc_categories',
			'hide_empty' => false,
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'meta_query' => array( 'order_clause' => array( 'key' => 'wpsc_category_load_order' ) ),
		] );
		?>
		<div class="form-group">
			<label
				for="wpsc_default_order_ticket_category"><?php _e( 'Default ticket category for order', 'supportcandy' ); ?></label>
			<p class="help-block"><?php _e( 'This category will get applied for newly created ticket.', 'supportcandy' ); ?></p>
			<select class="form-control" name="wpsc_default_order_ticket_category"
			        id="wpsc_default_order_ticket_category">
				<?php
				$wpsc_default_ticket_category = get_option( 'wpsc_default_order_ticket_category' );
				foreach ( $categories as $category ) :
					$selected = $wpsc_default_ticket_category == $category->term_id ? 'selected="selected"' : '';
					echo '<option ' . $selected . ' value="' . $category->term_id . '">' . $category->name . '</option>';
				endforeach;
				?>
			</select>
		</div>
		<div class="form-group">
			<label
				for="wpsc_default_order_ticket_category"><?php _e( 'Default ticket category for contact form', 'supportcandy' ); ?></label>
			<p class="help-block"><?php _e( 'This category will get applied for newly created ticket.', 'supportcandy' ); ?></p>
			<select class="form-control" name="wpsc_default_contact_form_ticket_category"
			        id="wpsc_default_contact_form_ticket_category">
				<?php
				$wpsc_default_ticket_category = get_option( 'wpsc_default_contact_form_ticket_category' );
				foreach ( $categories as $category ) :
					$selected = $wpsc_default_ticket_category == $category->term_id ? 'selected="selected"' : '';
					echo '<option ' . $selected . ' value="' . $category->term_id . '">' . $category->name . '</option>';
				endforeach;
				?>
			</select>
		</div>
		<?php
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
		return;
	}
}
