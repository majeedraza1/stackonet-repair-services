<?php

namespace Stackonet\Modules\MyAccount;

use Stackonet\Supports\ArrayHelper;

class TrackStatus {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'track-status';

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_filter( 'woocommerce_account_menu_items', [ self::$instance, 'add_menu_items' ], 11 );
			add_action( 'phone_repairs_asap_activation', [ self::$instance, 'add_endpoint' ] );
			add_action( 'init', array( self::$instance, 'add_endpoint' ) );
			add_filter( 'query_vars', array( self::$instance, 'query_vars' ), 0 );
			// Change the MyAccount page title.
			add_filter( 'the_title', array( self::$instance, 'endpoint_title' ) );
			// Inserting your new tab/page into the My Account page.
			add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint',
				array( self::$instance, 'endpoint_content' ) );
		}

		return self::$instance;
	}

	/**
	 * Add new menu items
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function add_menu_items( $items ) {
		if ( ! current_user_can( 'manage_phones' ) ) {
			return $items;
		}

		$new_items = [ self::$endpoint => __( 'Track Status', 'stackonet-repair-services' ), ];
		$items     = ArrayHelper::insert_after( $items, 'edit-account', $new_items );

		return $items;
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;
		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = 'Track Status';
			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Endpoint content
	 */
	public function endpoint_content() {
		echo '<div id="my-account-track-status"></div>';
	}
}
