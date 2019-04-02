<?php

namespace Stackonet\Modules\MyAccount;

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
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_my_account_scripts' ] );
		}

		return self::$instance;
	}

	public function load_my_account_scripts() {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			wp_enqueue_script( 'stackonet-repair-services-account' );
			wp_enqueue_style( 'stackonet-repair-services-account' );
		}
	}

	/**
	 * include my account files
	 */
	private function include_files() {
		Phones::init();
		TrackStatus::init();
	}
}
