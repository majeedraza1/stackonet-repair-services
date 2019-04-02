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
		}

		return self::$instance;
	}

	/**
	 * include my account files
	 */
	private function include_files() {
		Phones::init();
		TrackStatus::init();
	}
}
