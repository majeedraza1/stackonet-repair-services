<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\Models\CheckoutAnalysis;
use Stackonet\Supports\Logger;

defined( 'ABSPATH' ) || exit;

class CheckoutAnalysisToSupportTicket {

	/**
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp', [ self::$instance, 'checkout_analysis_cron_job' ] );
			add_action( 'checkout_analysis', [ self::$instance, 'checkout_analysis' ] );
		}

		return self::$instance;
	}

	/**
	 * Schedule Cron Job Event
	 */
	public static function checkout_analysis_cron_job() {
		if ( ! wp_next_scheduled( 'checkout_analysis' ) ) {
			wp_schedule_event( time(), 'hourly', 'checkout_analysis' );
		}
	}

	/**
	 * Scheduled Action Hook
	 * @throws Exception
	 */
	public static function checkout_analysis() {
		$tickets = ( new CheckoutAnalysis() )->needToAddSupport();
		foreach ( $tickets as $ticket ) {
			self::process( $ticket );
		}
	}

	/**
	 * Create order from lead
	 *
	 * @param CheckoutAnalysis $checkout_analysis
	 *
	 * @throws Exception
	 */
	public static function process( CheckoutAnalysis $checkout_analysis ) {
		$_data = [
			'ticket_subject'  => 'Checkout Analysis - ' . $checkout_analysis->get_full_name(),
			'customer_name'   => $checkout_analysis->get_full_name(),
			'customer_phone'  => $checkout_analysis->get_phone(),
			'customer_email'  => '',
			'city'            => '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'support_ticket_default_checkout_analysis_category' ),
			'agent_created'   => 0,
		];

		ob_start();
		?>
		<table class="table--support-order">
			<tr>
				<td>Name:</td>
				<td><?php echo $checkout_analysis->get_full_name(); ?></td>
			</tr>
			<tr>
				<td>Phone:</td>
				<td><?php echo $checkout_analysis->get_phone(); ?></td>
			</tr>
		</table>
		<?php
		$content = ob_get_clean();

		$supportTicket = new SupportTicket();
		$ticket_id     = $supportTicket->create_support_ticket( $_data, $content );
		if ( $ticket_id ) {
			$supportTicket->update_metadata( $ticket_id, 'created_via', 'checkout_analysis' );
			$supportTicket->update_metadata( $ticket_id, 'belongs_to_id', $checkout_analysis->get( 'id' ) );

			$checkout_analysis->update( [
				'id'                => $checkout_analysis->get( 'id' ),
				'support_ticket_id' => $ticket_id
			] );
		}
	}
}
