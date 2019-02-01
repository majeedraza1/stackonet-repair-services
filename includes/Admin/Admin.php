<?php

namespace Stackonet\Admin;

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

			add_action( 'admin_menu', [ self::$instance, 'add_menu' ] );
		}

		return self::$instance;
	}

	/**
	 * Add top level menu
	 */
	public static function add_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'vue-wp-starter';

		$hook = add_menu_page( __( 'Vue App', 'vue-wp-starter' ), __( 'Vue App', 'vue-wp-starter' ),
			$capability, $slug, [ self::$instance, 'menu_page_callback' ], 'dashicons-update', 6 );

		$menus = [
			[ 'title' => __( 'Vue App', 'vue-wp-starter' ), 'slug' => '#/' ],
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
		wp_enqueue_style( 'vue-wp-starter-admin' );
		wp_enqueue_script( 'vue-wp-starter-admin' );
		wp_localize_script( 'vue-wp-starter-admin', 'vueWpStarterSettings', array(
			'root'  => esc_url_raw( rest_url( 'vue-wp-starter/v1' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );
	}
}
