<?php

namespace Stackonet\Modules\MyAccount;

use Stackonet\Models\Phone;

defined( 'ABSPATH' ) || exit;

class MyAccount {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->include_files();
			add_filter( 'woocommerce_account_menu_items', [ self::$instance, 'account_menu_items' ] );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_my_account_scripts' ] );
		}

		return self::$instance;
	}

	/**
	 * Modify menu item
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function account_menu_items( $items ) {
		if ( isset( $items['downloads'] ) ) {
			unset( $items['downloads'] );
		}

		// Hide edit address menu for shop manager.
		if ( self::is_manager() ) {
			if ( isset( $items['edit-address'] ) ) {
				unset( $items['edit-address'] );
			}
		}

		return $items;
	}

	/**
	 * Load my account related scripts
	 */
	public function load_my_account_scripts() {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			wp_enqueue_script( 'stackonet-repair-services-frontend' );
			wp_enqueue_style( 'stackonet-repair-services-frontend' );
			wp_localize_script( 'stackonet-repair-services-frontend', 'StackonetRentCenter', [
				'phone_statuses' => Phone::available_status(),
			] );
		}
	}

	/**
	 * include my account files
	 */
	private function include_files() {
		Phones::init();
		TrackStatus::init();
		ManagerStoreAddress::init();
	}

	/**
	 * Check if current user is manager
	 *
	 * @return bool
	 */
	public static function is_manager() {
		return ( current_user_can( 'manage_phones' ) && ! current_user_can( 'manage_options' ) );
	}
}
