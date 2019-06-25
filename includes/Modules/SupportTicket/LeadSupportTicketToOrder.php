<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\Ajax;
use Stackonet\Models\Appointment;
use WC_Data_Exception;
use WC_Order;
use WC_Order_Item_Fee;

defined( 'ABSPATH' ) or exit;

class LeadSupportTicketToOrder {

	/**
	 * Create order from lead
	 *
	 * @param Appointment $appointment
	 * @param int $support_ticket_id
	 *
	 * @return int
	 * @throws WC_Data_Exception
	 * @throws Exception
	 */
	public static function process( Appointment $appointment, $support_ticket_id ) {
		// Now we create the order
		$order = new WC_Order();

		$address        = $appointment->get( 'address' );
		$street_number  = isset( $address['street_number']['short_name'] ) ? $address['street_number']['short_name'] : '';
		$street_address = isset( $address['street_address']['long_name'] ) ? $address['street_address']['long_name'] : '';
		$_address       = [
			'first_name' => '',
			'last_name'  => '',
			'company'    => $appointment->get( 'store_name' ),
			'address_1'  => $street_number . ' ' . $street_address,
			'address_2'  => '',
			'city'       => isset( $address['city']['long_name'] ) ? $address['city']['long_name'] : '',
			'state'      => isset( $address['state']['short_name'] ) ? $address['state']['short_name'] : '',
			'postcode'   => isset( $address['postal_code']['short_name'] ) ? $address['postal_code']['short_name'] : '',
			'country'    => isset( $address['country']['short_name'] ) ? $address['country']['short_name'] : '',
			'email'      => $appointment->get( 'email' ),
			'phone'      => $appointment->get( 'phone' ),
		];

		// Set billing address
		$order->set_address( $_address, 'billing' );

		// Set shipping address
		$order->set_address( $_address, 'shipping' );

		$order->set_customer_id( 0 );

		$product_id   = intval( $appointment->get( 'product_id' ) );
		$device_id    = $appointment->get( 'device_id' );
		$device_color = $appointment->get( 'device_color' );

		// Add Product
		$product = wc_get_product( $product_id );
		$item_id = $order->add_product( $product, 1, [
			'subtotal' => 0,
			'total'    => 0,
		] );
		wc_update_order_item_meta( $item_id, '_device_id', $device_id );
		wc_update_order_item_meta( $item_id, '_device_title', $appointment->get( 'device' ) );
		wc_update_order_item_meta( $item_id, '_device_model', $appointment->get( 'device_model' ) );
		wc_update_order_item_meta( $item_id, '_device_color', $device_color );

		$device_issues = [];
		$total_amount  = 0;
		$issues        = $appointment->get( 'device_issues' );
		$has_discount  = count( $issues ) >= 2;

		// Add Issue
		foreach ( $issues as $issue ) {
			$item_price = floatval( $issue['price'] );
			$item_tax   = $item_price * 0.07;
			$item_fee   = new WC_Order_Item_Fee();
			$item_fee->set_name( sanitize_text_field( $issue['title'] ) );
			$item_fee->set_total( $item_price );
			$item_fee->set_total_tax( $item_tax );
			$item_fee->set_order_id( $order->get_id() );
			$item_fee->save();
			$order->add_item( $item_fee );
			$total_amount += $item_price;

			$device_issues[] = sanitize_text_field( $issue['title'] );
		}

		$order->add_meta_data( '_preferred_service_date', $appointment->get( 'appointment_date' ) );
		$order->add_meta_data( '_preferred_service_time_range', $appointment->get( 'appointment_time' ) );
		$order->add_meta_data( '_additional_address', '' );
		$order->add_meta_data( '_signature_image', '' );

		// Add device data for SMS
		$order->add_meta_data( '_device_id', $device_id );
		$order->add_meta_data( '_device_title', $appointment->get( 'device' ) );
		$order->add_meta_data( '_device_model', $appointment->get( 'device_model' ) );
		$order->add_meta_data( '_device_color', $device_color );
		$order->add_meta_data( '_device_issues', $device_issues );

		// Add unique id for reschedule
		$order->add_meta_data( '_reschedule_hash', bin2hex( random_bytes( 20 ) ) );
		$order->add_meta_data( '_lead_id', $appointment->get( 'id' ) );
		$order->add_meta_data( '_support_ticket_id', $support_ticket_id );

		$order->save_meta_data();

		$calculate_tax_args = array(
			'country'  => $order->get_shipping_country(),
			'state'    => $order->get_shipping_state(),
			'postcode' => $order->get_shipping_postcode(),
			'city'     => $order->get_shipping_city(),
		);

		// Calculate totals and save data
		$order->calculate_taxes( $calculate_tax_args );
		$order->calculate_totals( false );
		if ( ! $has_discount ) {
			$order->set_status( 'on-hold' );
		}
		$order->save();

		// Add Discount
		if ( $has_discount ) {
			( new Ajax() )->add_order_discount( $order->get_id(), 'Fixed Discount (15%)', '15%' );
		}

		self::add_support_ticket_note( $order, $support_ticket_id );

		return $order->get_id();
	}

	/**
	 * @param WC_Order $order
	 * @param int $support_ticket_id
	 *
	 * @throws Exception
	 */
	private static function add_support_ticket_note( WC_Order $order, $support_ticket_id ) {
		$content = OrderToSupportTicket::get_support_ticket_content( $order );
		( new SupportTicket() )->add_ticket_info( $support_ticket_id, [
			'thread_type'    => 'report',
			'customer_name'  => $order->get_billing_company(),
			'customer_email' => $order->get_billing_phone(),
			'post_content'   => $content,
			'agent_created'  => 0,
		] );
	}
}
