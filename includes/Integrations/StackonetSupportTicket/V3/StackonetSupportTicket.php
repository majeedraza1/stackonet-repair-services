<?php

namespace Stackonet\Integrations\StackonetSupportTicket\V3;

use Stackonet\Integrations\Twilio;

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

			add_action( 'stackonet_support_ticket/v3/send_short_message',
				[ self::$instance, 'send_short_message' ], 10, 2 );
		}

		return self::$instance;
	}

	/**
	 * Send short message
	 *
	 * @param string $content
	 * @param array $phones
	 */
	public function send_short_message( $content, array $phones ) {
		( new Twilio() )->send_support_ticket_sms( $phones, $content );
	}
}
