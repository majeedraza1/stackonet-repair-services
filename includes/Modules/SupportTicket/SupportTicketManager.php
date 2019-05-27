<?php

namespace Stackonet\Modules\SupportTicket;

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

			SupportTicketController::init();
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
}
