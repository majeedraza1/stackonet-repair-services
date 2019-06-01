<?php

namespace Stackonet\Modules\SupportTicket;

use Stackonet\Supports\Logger;
use Stackonet\Supports\Utils;

defined( 'ABSPATH' ) or exit;

class SupportTicketManager {

	/**
	 * @var null
	 */
	public static $instance = null;

	/**
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'wpsc_get_gerneral_settings', [ self::$instance, 'settings' ] );
			add_action( 'wpsc_set_gerneral_settings', [ self::$instance, 'save_settings' ] );
			add_shortcode( 'stackonet_support_ticket_form', [ self::$instance, 'support_ticket_form' ] );
			add_action( 'wp_ajax_download_support_ticket', [ self::$instance, 'download_support_ticket' ] );

			SupportTicketController::init();
		}

		return self::$instance;
	}

	public function download_support_ticket() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You have no permission to download phones CSV file.' );
		}

		$ticket_status   = isset( $_GET['ticket_status'] ) ? $_GET['ticket_status'] : '';
		$ticket_category = isset( $_GET['ticket_category'] ) ? $_GET['ticket_category'] : '';
		$ticket_priority = isset( $_GET['ticket_priority'] ) ? $_GET['ticket_priority'] : '';

		/** @var TicketStatus[] $_statuses */
		$_statuses = TicketStatus::get_all();
		/** @var TicketCategory[] $_categories */
		$_categories = TicketCategory::get_all();
		/** @var TicketPriority[] $_priorities */
		$_priorities = TicketPriority::get_all();
		/** @var SupportAgent[] $_agents */
		$_agents = SupportAgent::get_all();

		$statuses = $categories = $priorities = $agents = [];

		foreach ( $_statuses as $status ) {
			$statuses[ $status->get( 'term_id' ) ] = $status->to_array();
		}

		foreach ( $_priorities as $status ) {
			$priorities[ $status->get( 'term_id' ) ] = $status->to_array();
		}

		foreach ( $_categories as $status ) {
			$categories[ $status->get( 'term_id' ) ] = $status->to_array();
		}

		foreach ( $_agents as $agent ) {
			$agents[ $agent->get_user_id() ] = $agent->to_array();
		}

		$items = ( new SupportTicket )->find( [
			'paged'           => 1,
			'per_page'        => 1000,
			'ticket_status'   => $ticket_status,
			'ticket_category' => $ticket_category,
			'ticket_priority' => $ticket_priority,
		] );

		$filename = sprintf( 'support-tickets-%s-%s-%s.csv', $ticket_status, $ticket_category, $ticket_priority );

		$header = [
			'Ticket ID',
			'Subject',
			'Status',
			'Name',
			'Email Address',
			'Assigned Agents',
			'Category',
			'Priority',
			'Created',
		];

		$rows = [ $header ];

		/** @var SupportTicket[] $items */
		foreach ( $items as $ticket ) {
			$status   = $ticket->get( 'ticket_status' );
			$status   = isset( $statuses[ $status ] ) ? $statuses[ $status ]['name'] : $status;
			$category = $ticket->get( 'ticket_category' );
			$category = isset( $categories[ $category ] ) ? $categories[ $category ]['name'] : $category;
			$priority = $ticket->get( 'ticket_priority' );
			$priority = isset( $priorities[ $priority ] ) ? $priorities[ $priority ]['name'] : $priority;

			$__agents   = [];
			$agents_ids = $ticket->get_assigned_agents_ids();
			foreach ( $agents_ids as $agent_id ) {
				if ( ! $agent_id ) {
					continue;
				}
				$__agents[] = isset( $agents[ $agent_id ] ) ? $agents[ $agent_id ]['display_name'] : '';

			}

			$rows[] = [
				$ticket->get( 'id' ),
				$ticket->get( 'ticket_subject' ),
				$status,
				$ticket->get( 'customer_name' ),
				$ticket->get( 'customer_email' ),
				implode( ', ', $__agents ),
				$category,
				$priority,
				$ticket->update_at(),
			];
		}

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/csv; charset=UTF-8' );
		@header( 'Content-Disposition: filename="' . $filename . '"' );
		@header( 'Expires: 0' );
		@header( 'Cache-Control: must-revalidate' );
		@header( 'Pragma: public' );
		echo Utils::generateCsv( $rows );
		die();
	}

	public function support_ticket_form() {
		$data         = [];
		$user         = wp_get_current_user();
		$data['user'] = [
			'display_name' => ! empty( $user->display_name ) ? $user->display_name : '',
			'user_email'   => ! empty( $user->user_email ) ? $user->user_email : '',
		];

		$categories  = [];
		$_categories = TicketCategory::get_all();
		$category    = get_option( 'wpsc_default_contact_form_ticket_category' );
		if ( is_array( $category ) && count( $category ) ) {
			foreach ( $_categories as $_category ) {
				if ( ! in_array( $_category->get( 'term_id' ), $category ) ) {
					continue;
				}

				$categories[] = [
					'id'   => $_category->get( 'term_id' ),
					'name' => $_category->get( 'name' ),
				];
			}
		}

		$data['categories'] = $categories;

		wp_localize_script( 'stackonet-repair-services-frontend', 'CustomerSupportTickets', $data );
		add_action( 'wp_footer', [ $this, 'tinymce_script' ], 9 );

		return '<div id="stackonet_support_ticket_form"></div>';
	}

	/**
	 * Script
	 */
	public function tinymce_script() {
		echo '<script type="text/javascript" src="' . includes_url( 'js/tinymce/tinymce.min.js' ) . '"></script>';
	}

	public function save_settings() {
		$order_ticket_category = isset( $_POST['wpsc_default_order_ticket_category'] ) ? sanitize_text_field( $_POST['wpsc_default_order_ticket_category'] ) : '';
		update_option( 'wpsc_default_order_ticket_category', $order_ticket_category );
		$order_ticket_category = isset( $_POST['wpsc_default_spot_appointment_category'] ) ? sanitize_text_field( $_POST['wpsc_default_spot_appointment_category'] ) : '';
		update_option( 'wpsc_default_spot_appointment_category', $order_ticket_category );
		$order_ticket_category = isset( $_POST['wpsc_default_contact_form_ticket_category'] ) ? $_POST['wpsc_default_contact_form_ticket_category'] : [];
		if ( is_array( $order_ticket_category ) && count( $order_ticket_category ) ) {
			$order_ticket_category = array_map( 'intval', $order_ticket_category );
			update_option( 'wpsc_default_contact_form_ticket_category', $order_ticket_category );
		}
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
				for="wpsc_default_order_ticket_category"><?php _e( 'Default ticket category for Spot Appointment', 'supportcandy' ); ?></label>
			<p class="help-block"><?php _e( 'This category will get applied for newly created Spot Appointment.', 'supportcandy' ); ?></p>
			<select class="form-control" name="wpsc_default_spot_appointment_category"
			        id="wpsc_default_spot_appointment_category">
				<?php
				$wpsc_default_ticket_category = get_option( 'wpsc_default_spot_appointment_category' );
				foreach ( $categories as $category ) :
					$selected = $wpsc_default_ticket_category == $category->term_id ? 'selected="selected"' : '';
					echo '<option ' . $selected . ' value="' . $category->term_id . '">' . $category->name . '</option>';
				endforeach;
				?>
			</select>
		</div>
		<div class="form-group">
			<label
				for="wpsc_default_order_ticket_category"><?php _e( 'Category for customer support form', 'supportcandy' ); ?></label>
			<p class="help-block"><?php _e( 'This category will get applied for customer support form.', 'supportcandy' ); ?></p>
			<?php
			$wpsc_default_ticket_category = (array) get_option( 'wpsc_default_contact_form_ticket_category' );
			foreach ( $categories as $category ) {
				$selected = in_array( $category->term_id, $wpsc_default_ticket_category ) ? 'checked' : '';
				?>
				<input type="checkbox" name="wpsc_default_contact_form_ticket_category[]"
				       value="<?php echo $category->term_id; ?>" <?php echo $selected; ?>> <?php echo $category->name; ?>
				<br>
			<?php } ?>
		</div>
		<?php
	}
}
