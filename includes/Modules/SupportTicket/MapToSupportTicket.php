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
			'ticket_category' => get_option( 'support_ticket_default_map_category' ),
			'agent_created'   => $map->author()->ID,
		];

		ob_start();
		?>
		<table class="table--support-order">
			<tr>
				<td>Title:</td>
				<td><strong><?php echo $map->get( 'title' ) ?></strong></td>
			</tr>
			<tr>
				<td>Travel Date:</td>
				<td><strong><?php echo $map->get( 'base_datetime' ) ?></strong></td>
			</tr>
			<tr>
				<td>Travel Mode:</td>
				<td><strong><?php echo $map->get( 'travel_mode' ) ?></strong></td>
			</tr>
			<tr>
				<td>Base Address:</td>
				<td><strong><?php echo $map->get( 'formatted_base_address' ) ?></strong></td>
			</tr>
		</table>
		<?php
		$content = ob_get_clean();

		$supportTicket = new SupportTicket();
		$ticket_id     = $supportTicket->create_support_ticket( $_data, $content );
		if ( $ticket_id ) {
			$supportTicket->update_metadata( $ticket_id, 'created_via', 'map' );
			$supportTicket->update_metadata( $ticket_id, 'belongs_to_id', $map->get( 'id' ) );

			$map->update( [ 'id' => $map->get( 'id' ), 'support_ticket_id' => $ticket_id ] );

			do_action( 'stackonet_support_ticket/v1/ticket_created', $ticket_id );
		}
	}
}
