<?php

namespace Stackonet\Modules\SupportTicket;

use Stackonet\Supports\Logger;

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

			SupportTicketController::init();
		}

		return self::$instance;
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
