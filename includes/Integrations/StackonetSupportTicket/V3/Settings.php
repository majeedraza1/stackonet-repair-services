<?php

namespace Stackonet\Integrations\StackonetSupportTicket\V3;

use StackonetSupportTicket\Admin\Settings as SupportTicketSettings;

class Settings {

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

			add_filter( 'stackonet_support_ticket/settings/fields', [ self::$instance, 'add_setting_fields' ] );
		}

		return self::$instance;
	}

	/**
	 * Add settings fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_setting_fields( $fields ) {
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_order_ticket_category',
			'type'              => 'select',
			'title'             => __( 'Default ticket category for order' ),
			'description'       => __( 'This category will get applied for newly created ticket.' ),
			'priority'          => 200,
			'sanitize_callback' => 'intval',
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_carrier_store_category',
			'type'              => 'select',
			'title'             => __( 'Default ticket category for carrier store' ),
			'description'       => __( 'This category will get applied for newly created carrier store ticket.' ),
			'priority'          => 205,
			'sanitize_callback' => 'intval',
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_spot_appointment_category',
			'type'              => 'select',
			'title'             => __( 'Default ticket category for Spot Appointment' ),
			'description'       => __( 'This category will get applied for newly created Spot Appointment ticket.' ),
			'priority'          => 205,
			'sanitize_callback' => 'intval',
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_checkout_analysis_category',
			'type'              => 'select',
			'title'             => __( 'Default ticket category for Checkout Analysis' ),
			'description'       => __( 'This category will get applied for newly created ticket from checkout analysis.' ),
			'priority'          => 205,
			'sanitize_callback' => 'intval',
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_map_category',
			'type'              => 'select',
			'title'             => __( 'Default ticket category for Map' ),
			'description'       => __( 'This category will get applied for newly created ticket from map.' ),
			'priority'          => 205,
			'sanitize_callback' => 'intval',
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_default_contact_form_category',
			'type'              => 'select',
			'title'             => __( 'Category for customer support form' ),
			'description'       => __( 'This category will get applied for customer support form.' ),
			'priority'          => 205,
			'multiple'          => true,
			'sanitize_callback' => function ( $value ) {
				return array_map( 'intval', $value );
			},
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);
		$fields[] = array(
			'section'           => 'general_settings_section',
			'id'                => 'support_ticket_search_categories',
			'type'              => 'select',
			'title'             => __( 'Categories for ticket search' ),
			'description'       => __( 'This category will get applied for admin support ticket list page search dropdown.' ),
			'priority'          => 205,
			'multiple'          => true,
			'sanitize_callback' => function ( $value ) {
				return array_map( 'intval', $value );
			},
			'options'           => SupportTicketSettings::get_tickets_categories_for_options(),
		);

		return $fields;
	}
}
