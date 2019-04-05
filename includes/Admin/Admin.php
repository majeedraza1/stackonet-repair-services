<?php

namespace Stackonet\Admin;

use Stackonet\Models\Settings;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Product;

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

			add_action( 'admin_enqueue_scripts', [ self::$instance, 'admin_scripts' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_menu' ] );
			add_action( 'admin_menu', [ self::$instance, 'add_rent_a_center_menu' ] );

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
	public function admin_scripts() {
		wp_localize_script( 'jquery', 'stackonetSettings', array(
			'root'     => esc_url_raw( rest_url( 'stackonet/v1' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'settings' => Settings::get_settings(),
		) );
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

		$hook = add_menu_page( __( 'Phone Repairs', 'vue-wp-starter' ), __( 'Phone Repairs', 'vue-wp-starter' ),
			$capability, $slug, [ self::$instance, 'menu_page_callback' ], 'dashicons-tablet', 6 );

		$menus = [
			[ 'title' => __( 'Devices', 'vue-wp-starter' ), 'slug' => '#/' ],
			[ 'title' => __( 'Service Areas', 'vue-wp-starter' ), 'slug' => '#/areas' ],
			[ 'title' => __( 'Issues', 'vue-wp-starter' ), 'slug' => '#/issues' ],
			[ 'title' => __( 'Requested Areas', 'vue-wp-starter' ), 'slug' => '#/requested-areas' ],
			[ 'title' => __( 'Testimonials', 'vue-wp-starter' ), 'slug' => '#/testimonial' ],
			[ 'title' => __( 'Settings', 'vue-wp-starter' ), 'slug' => '#/settings' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'init_hooks' ] );
	}

	/**
	 * Menu page callback
	 */
	public static function menu_page_callback() {
		echo '<div class="wrap"><div id="vue-wp-starter"></div></div>';
	}

	/**
	 * Load required styles and scripts
	 */
	public static function init_hooks() {
		wp_enqueue_media();
		wp_enqueue_style( 'stackonet-repair-services-admin' );
		wp_enqueue_script( 'stackonet-repair-services-admin' );
	}

	/**
	 * Add Rent a center menu
	 */
	public static function add_rent_a_center_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'rent-a-center';

		$hook = add_menu_page( __( 'Rent a Center', 'stackonet-repair-services' ), __( 'Rent a Center', 'stackonet-repair-services' ),
			$capability, $slug, [ self::$instance, 'rent_a_center_callback' ], 'dashicons-cart', 6.66 );

		$menus = [
			[ 'title' => __( 'Phones', 'vue-wp-starter' ), 'slug' => '#/' ],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'init_rent_a_center_hooks' ] );
	}

	/**
	 * Rent a Center page callback
	 */
	public function rent_a_center_callback() {
		echo '<div class="wrap"><div id="rent-a-center"></div></div>';
	}

	/**
	 * Admin menu page scripts
	 */
	public function init_rent_a_center_hooks() {
		wp_enqueue_style( 'stackonet-repair-services-admin' );
		wp_enqueue_style( 'stackonet-repair-services-rent-center' );
		wp_enqueue_script( 'stackonet-repair-services-rent-center' );
	}
}
