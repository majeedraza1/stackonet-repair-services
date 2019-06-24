<?php

namespace Stackonet\Admin;

use Stackonet\Models\Phone;
use Stackonet\Models\Settings;
use Stackonet\Modules\SupportTicket\SupportAgent;
use Stackonet\Modules\SupportTicket\SupportTicket;
use Stackonet\Modules\SupportTicket\TicketCategory;
use Stackonet\Modules\SupportTicket\TicketPriority;
use Stackonet\Modules\SupportTicket\TicketStatus;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Product;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class Admin {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'admin_enqueue_scripts', [ self::$instance, 'admin_localize_scripts' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_rent_a_center_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_survey_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_carrier_store_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_become_technician_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_spot_appointment_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_support_tickets_menu' ], 9 );

			// Add decoration product data on Checkout(after), Email and Order Detail Page
			add_action( 'woocommerce_order_item_meta_start', [ self::$instance, 'order_item_meta_start' ], 10, 3 );

			// Add decoration product data on WC Order at admin
			add_filter( 'woocommerce_hidden_order_itemmeta', [ self::$instance, 'hide_order_item_meta_fields' ] );
			add_action( 'woocommerce_after_order_itemmeta', [ self::$instance, 'show_order_item_meta_fields' ], 10, 3 );
		}

		return self::$instance;
	}

	/**
	 * Admin scripts
	 */
	public function admin_localize_scripts() {
		/** @var WP_Post[] $pages */
		$pages  = get_pages();
		$_pages = [];
		foreach ( $pages as $page ) {
			$_pages[] = [ 'id' => $page->ID, 'title' => $page->post_title ];
		}
		wp_localize_script( 'jquery', 'stackonetSettings', array(
			'root'     => esc_url_raw( rest_url( 'stackonet/v1' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'settings' => Settings::get_settings(),
			'pages'    => $_pages,
		) );
	}

	/**
	 * Admin menu page scripts
	 */
	public function load_admin_scripts() {
		wp_enqueue_media();
		wp_enqueue_style( 'stackonet-repair-services-admin' );
		wp_enqueue_script( 'stackonet-repair-services-admin' );
	}

	/**
	 * Load tinymce scripts
	 */
	public function tinymce_script() {
		echo '<script type="text/javascript" src="' . includes_url( 'js/tinymce/tinymce.min.js' ) . '"></script>';
	}

	/**
	 * Add decoration and custom data to product item on
	 * Checkout, Email and Order Detail
	 *
	 * @param int $item_id
	 * @param WC_Order_Item_Product $item
	 * @param array $order
	 *
	 * @return void
	 */
	public function order_item_meta_start( $item_id, $item, $order ) {
		$device_title = $item->get_meta( '_device_title', true );
		$device_model = $item->get_meta( '_device_model', true );
		$device_color = $item->get_meta( '_device_color', true );
		$meta_data    = [
			// [ 'key' => 'Device', 'value' => $device_title ],
			[ 'key' => 'Model', 'value' => $device_model ],
			[ 'key' => 'Color', 'value' => $device_color ],
		];

		echo '<ul class="wc-item-meta">';
		foreach ( $meta_data as $meta ) {
			echo '<li><strong class="wc-item-meta-label">' . $meta['key'] . ': </strong>' . $meta['value'] . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Hide order item meta fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function hide_order_item_meta_fields( $fields ) {
		$fields[] = '_device_id';
		$fields[] = '_device_title';
		$fields[] = '_device_model';
		$fields[] = '_device_color';

		return $fields;
	}

	/**
	 * Show on admin order item meta
	 *
	 * @param int $item_id
	 * @param WC_Order_Item $item
	 * @param WC_Product $product
	 */
	public function show_order_item_meta_fields( $item_id, $item, $product ) {
		$device_title = $item->get_meta( '_device_title', true );
		$device_model = $item->get_meta( '_device_model', true );
		$device_color = $item->get_meta( '_device_color', true );

		$meta_data = [
			// [ 'key' => 'Device', 'value' => $device_title ],
			[ 'key' => 'Model', 'value' => $device_model ],
			[ 'key' => 'Color', 'value' => $device_color ],
		];
		?>
		<div class="view">
			<table cellspacing="0" class="display_meta">
				<?php
				foreach ( $meta_data as $meta ) : ?>
					<tr>
						<th><?php echo wp_kses_post( $meta['key'] ); ?>:</th>
						<td><?php echo wp_kses_post( force_balance_tags( $meta['value'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php
	}

	/**
	 * Add top level menu
	 */
	public static function add_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'phone-repairs';

		$hook = add_menu_page( __( 'Phone Repairs', 'stackonet-repair-services' ), __( 'Phone Repairs', 'stackonet-repair-services' ),
			$capability, $slug, function () {
				echo '<div class="wrap"><div id="stackonet-repair-services-admin"></div></div>';
			}, 'dashicons-tablet', 6 );

		$menus = [
			[ 'title' => __( 'Devices', 'stackonet-repair-services' ), 'slug' => '#/' ],
			[ 'title' => __( 'Service Areas', 'stackonet-repair-services' ), 'slug' => '#/areas' ],
			[ 'title' => __( 'Issues', 'stackonet-repair-services' ), 'slug' => '#/issues' ],
			[ 'title' => __( 'Requested Areas', 'stackonet-repair-services' ), 'slug' => '#/requested-areas' ],
			[ 'title' => __( 'Testimonials', 'stackonet-repair-services' ), 'slug' => '#/testimonial' ],
			[ 'title' => __( 'Settings', 'stackonet-repair-services' ), 'slug' => '#/settings' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'load_admin_scripts' ] );
	}

	/**
	 * Add Rent a center menu
	 */
	public static function add_rent_a_center_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'rent-a-center';

		$hook = add_menu_page( __( 'Rent a Center', 'stackonet-repair-services' ), __( 'Rent a Center', 'stackonet-repair-services' ),
			$capability, $slug, function () {
				echo '<div class="wrap"><div id="rent-a-center"></div></div>';
			}, 'dashicons-admin-post', 7 );

		$menus = [
			[ 'title' => __( 'Phones', 'stackonet-repair-services' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'init_rent_a_center_hooks' ] );
	}

	/**
	 * Admin menu page scripts
	 */
	public function init_rent_a_center_hooks() {
		wp_enqueue_style( 'stackonet-repair-services-admin' );
		wp_enqueue_script( 'stackonet-repair-services-admin' );
		wp_localize_script( 'jquery', 'StackonetRentCenter', [
			'phone_statuses'         => Phone::available_status(),
			'unique_store_addresses' => Phone::unique_store_address(),
		] );
	}

	/**
	 * Add survey menu
	 */
	public static function add_survey_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'survey';

		$hook = add_menu_page( __( 'Survey', 'stackonet-repair-services' ), __( 'Survey', 'stackonet-repair-services' ),
			$capability, $slug, function () {
				echo '<div class="wrap"><div id="admin-stackonet-survey"></div></div>';
			}, 'dashicons-admin-post', 8 );

		$menus = [
			[ 'title' => __( 'Survey', 'stackonet-repair-services' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'load_admin_scripts' ] );
	}

	/**
	 * Add carrier store menu
	 */
	public static function add_carrier_store_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'carrier-store';

		$hook = add_menu_page(
			__( 'Carrier Store', 'stackonet-repair-services' ),
			__( 'Carrier Store', 'stackonet-repair-services' ),
			$capability, $slug, function () {
			echo '<div class="wrap"><div id="admin-stackonet-carrier-store"></div></div>';
		}, 'dashicons-admin-post', 8 );

		$menus = [
			[ 'title' => __( 'Carrier Store', 'stackonet-repair-services' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'load_admin_scripts' ] );
	}

	/**
	 * Become a technician menu
	 */
	public function add_become_technician_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'become-technician';

		$hook = add_menu_page( __( 'Become A Technician', 'stackonet-repair-services' ), __( 'Become A Technician', 'stackonet-repair-services' ),
			$capability, $slug, function () {
				echo '<div class="wrap"><div id="admin-stackonet-become-technician"></div></div>';
			}, 'dashicons-admin-post', 8 );

		$menus = [
			[ 'title' => __( 'Technicians', 'stackonet-repair-services' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'load_admin_scripts' ] );
	}

	/**
	 * Become a technician menu
	 */
	public function add_spot_appointment_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'spot-appointment';

		$hook = add_menu_page( __( 'Lead', 'stackonet-repair-services' ), __( 'Lead', 'stackonet-repair-services' ),
			$capability, $slug, function () {
				echo '<div class="wrap"><div id="admin-stackonet-spot-appointment"></div></div>';
			}, 'dashicons-admin-post', 8 );

		$menus = [
			[ 'title' => __( 'Lead', 'stackonet-repair-services' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'load_admin_scripts' ] );
	}

	/**
	 * Support tickets menu
	 */
	public function add_support_tickets_menu() {
		$capability = 'manage_options';
		$slug       = 'wpsc-tickets';

		$hook = add_menu_page( __( 'Support', 'stackonet-repair-services' ), __( 'Support', 'stackonet-repair-services' ),
			$capability, $slug, [ self::$instance, 'support_tickets_callback' ], 'dashicons-admin-post', 8 );

		add_action( 'load-' . $hook, [ self::$instance, 'init_support_tickets_hooks' ] );
	}

	/**
	 * Become a technician menu callback
	 */
	public function support_tickets_callback() {
		add_action( 'admin_footer', [ $this, 'tinymce_script' ], 9 );
		echo '<div class="wrap"><div id="admin-stackonet-support-tickets"></div></div>';
	}

	/**
	 * Admin menu page scripts
	 */
	public function init_support_tickets_hooks() {
		wp_enqueue_style( 'stackonet-repair-services-admin' );
		wp_enqueue_script( 'stackonet-repair-services-admin' );

		$data          = [];
		$supportTicket = new SupportTicket();

		$_statuses        = $supportTicket->get_ticket_statuses_terms();
		$data['statuses'] = [ [ 'key' => 'all', 'label' => 'All Statuses', 'count' => 0, 'active' => true ] ];
		foreach ( $_statuses as $status ) {
			$data['statuses'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}
		$data['statuses'][] = [ 'key' => 'trash', 'label' => 'Trash', 'count' => 0, 'active' => false ];


		$_categories        = $supportTicket->get_categories_terms();
		$data['categories'] = [ [ 'key' => 'all', 'label' => 'All Categories', 'count' => 0, 'active' => true ] ];
		foreach ( $_categories as $status ) {
			$data['categories'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$_priorities        = $supportTicket->get_priorities_terms();
		$data['priorities'] = [ [ 'key' => 'all', 'label' => 'All Priorities', 'count' => 0, 'active' => true ] ];
		foreach ( $_priorities as $status ) {
			$data['priorities'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$data['count_trash'] = $supportTicket->count_trash_records();

		$data['ticket_categories'] = TicketCategory::get_all();
		$data['ticket_statuses']   = TicketStatus::get_all();
		$data['ticket_priorities'] = TicketPriority::get_all();
		$data['support_agents']    = SupportAgent::get_all();

		$user           = wp_get_current_user();
		$data['user']   = [
			'display_name' => $user->display_name,
			'user_email'   => $user->user_email,
		];
		$data['cities'] = ( new SupportTicket() )->find_all_cities();

		$data['search_categories'] = (array) get_option( 'stackonet_ticket_search_categories' );

		wp_localize_script( 'stackonet-repair-services-admin', 'SupportTickets', $data );
	}
}
