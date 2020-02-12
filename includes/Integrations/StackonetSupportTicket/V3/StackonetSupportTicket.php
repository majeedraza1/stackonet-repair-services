<?php

namespace Stackonet\Integrations\StackonetSupportTicket\V3;

class StackonetSupportTicket {
	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			Settings::init();
		}

		return self::$instance;
	}
}
