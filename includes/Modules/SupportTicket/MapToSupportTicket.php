<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\Models\Map;

defined( 'ABSPATH' ) or exit;

class MapToSupportTicket {
	/**
	 * Process appointment to support ticket conversion
	 *
	 * @param Map $map
	 *
	 * @throws Exception
	 */
	public static function process( Map $map ) {
		$_data = [
			'ticket_subject'  => 'Map Route : ' . $map->get( 'title' ),
			'customer_name'   => $map->author()->display_name,
			'customer_email'  => $map->author()->user_email,
			'customer_phone'  => '',
			'city'            => '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'wpsc_default_spot_appointment_category' ),
			'agent_created'   => $map->author()->ID,
		];

		$content = '';

		$supportTicket = new SupportTicket();
		$ticket_id     = $supportTicket->create_support_ticket( $_data, $content );
		if ( $ticket_id ) {
			$supportTicket->update_metadata( $ticket_id, 'created_via', 'map' );
			$supportTicket->update_metadata( $ticket_id, 'belongs_to_id', $map->get( 'id' ) );

			$map->update( [ 'id' => $map->get( 'id' ), 'support_ticket_id' => $ticket_id ] );
		}
	}
}
