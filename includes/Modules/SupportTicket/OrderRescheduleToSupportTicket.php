<?php

namespace Stackonet\Modules\SupportTicket;

use WC_Order;

defined( 'ABSPATH' ) or exit;

class OrderRescheduleToSupportTicket {
	/**
	 * Process order to support ticket conversion
	 *
	 * @param WC_Order $order
	 * @param array $data
	 */
	public static function process( WC_Order $order, array $data ) {
		$support_ticket_id = $order->get_meta( '_support_ticket_id' );
		$created_by        = ( $data['created_by'] === 'admin' ) ? 'Admin' : 'Customer';

		ob_start();
		echo "Order has been reschedule by <strong>{$created_by}</strong>. New date and time are<br>";
		echo "Date: <strong>{$data['date']}</strong><br>";
		echo "Time: <strong>{$data['time']}</strong>";
		$post_content = ob_get_clean();

		( new SupportTicket() )->add_ticket_info( $support_ticket_id, [
			'thread_type'    => 'note',
			'customer_name'  => $order->get_formatted_billing_full_name(),
			'customer_email' => $order->get_billing_email(),
			'post_content'   => $post_content,
			'agent_created'  => 0,
		] );
	}
}
