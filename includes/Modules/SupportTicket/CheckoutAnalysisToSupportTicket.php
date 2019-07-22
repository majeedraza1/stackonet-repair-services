<?php

namespace Stackonet\Modules\SupportTicket;

use Stackonet\Models\CheckoutAnalysis;

defined( 'ABSPATH' ) || exit;

class CheckoutAnalysisToSupportTicket {

	/**
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public function init() {
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
			wp_schedule_event( time(), 'hourly', 'checkout_analysis', array( 'data' ) );
		}
	}

	/**
	 * Scheduled Action Hook
	 *
	 * @param string $param1
	 */
	public static function checkout_analysis( $param1 = 'data' ) {
		// Checkout Analysis
	}

	/**
	 * Create order from lead
	 *
	 * @param CheckoutAnalysis $checkout_analysis
	 */
	public static function process( CheckoutAnalysis $checkout_analysis ) {
		var_dump( $checkout_analysis->extra_information() );
		die();
		$content = '';
		$_data   = [
			'ticket_subject'  => 'Checkout Analysis - ' . $checkout_analysis->get( 'store_name' ) . ' - ' . implode( ', ', $issues ),
			'customer_name'   => $appointment->get( 'store_name' ),
			'customer_email'  => $appointment->get( 'email' ),
			'customer_phone'  => $appointment->get( 'phone' ),
			'city'            => ! empty( $address['city']['long_name'] ) ? $address['city']['long_name'] : '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'wpsc_default_spot_appointment_category' ),
			'agent_created'   => $appointment->get( 'created_by' ),
		];

		$supportTicket = new SupportTicket();
		$ticket_id     = $supportTicket->create_support_ticket( $_data, $content );
		if ( $ticket_id ) {
			$supportTicket->update_metadata( $ticket_id, 'created_via', 'appointment' );
			$supportTicket->update_metadata( $ticket_id, 'belongs_to_id', $appointment->get( 'id' ) );
		}
	}
}
