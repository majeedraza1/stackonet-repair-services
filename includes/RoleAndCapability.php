<?php

namespace Stackonet;

use WP_Role;

defined( 'ABSPATH' ) || exit;

class RoleAndCapability {

	/**
	 * Dashboard capabilities
	 *
	 * @var array
	 */
	protected static $dashboard_capabilities = [
		'read_reports',
	];

	/**
	 * Support Ticket capabilities
	 *
	 * @var array
	 */
	protected static $ticket_capabilities = [
		'delete_others_tickets',
		'delete_tickets',
		'edit_others_tickets',
		'edit_tickets',
		'create_tickets',
		'read_others_tickets',
		'read_tickets',
	];

	/**
	 * Survey Capability
	 *
	 * @var array
	 */
	protected static $survey_capabilities = [
		'delete_others_surveys',
		'delete_surveys',
		'edit_others_surveys',
		'edit_surveys',
		'create_surveys',
		'read_others_surveys',
		'read_surveys',
		// Backup capabilities
		'add_survey',
	];

	/**
	 * Device capabilities
	 *
	 * @var array
	 */
	protected static $device_capabilities = [
		'delete_others_devices',
		'delete_devices',
		'edit_others_devices',
		'edit_devices',
		'create_devices',
		'read_others_devices',
		'read_devices',
		// Backup capabilities
		'manage_phones',
	];

	/**
	 * Checkout Analysis capabilities
	 *
	 * @var array
	 */
	protected static $checkout_analysis_capabilities = [
		'read_checkout_analysis_records',
	];

	/**
	 * Twilio message capabilities
	 *
	 * @var array
	 */
	protected static $twilio_message_capabilities = [
		'delete_twilio_messages',
		'edit_twilio_messages',
		'create_twilio_messages',
		'read_twilio_messages',
	];

	/**
	 * Method to run on plugin activation
	 */
	public static function activation() {
		self::add_manager_role();
		self::add_agent_role();
		self::add_survey_capabilities();
		self::add_device_capabilities();
		self::add_support_ticket_capabilities();
		self::add_checkout_analysis_capabilities();
		self::add_twilio_message_capabilities();
		self::add_dashboard_capabilities();
	}

	/**
	 * Add manager role
	 */
	public static function add_manager_role() {
		if ( ! get_role( 'manager' ) ) {
			add_role( 'manager', 'Manager', [ 'read' => true ] );
		}
	}

	/**
	 * Add agent role
	 */
	public static function add_agent_role() {
		if ( ! get_role( 'agent' ) ) {
			add_role( 'agent', 'Agent', [ 'read' => true ] );
		}
	}

	/**
	 * Add survey capabilities
	 */
	public static function add_survey_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor', 'manager', 'agent' ],
			array_fill_keys( self::$survey_capabilities, true )
		);
	}

	/**
	 * Add device capabilities
	 */
	public static function add_device_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor' ],
			array_fill_keys( self::$device_capabilities, true )
		);
	}

	/**
	 * Add support ticket capabilities
	 */
	public static function add_support_ticket_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor', 'manager' ],
			array_fill_keys( self::$ticket_capabilities, true )
		);
	}

	/**
	 * Add checkout analysis capabilities
	 */
	private static function add_checkout_analysis_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor', 'manager' ],
			array_fill_keys( self::$checkout_analysis_capabilities, true )
		);
	}

	/**
	 * Add twilio messages capabilities
	 */
	private static function add_twilio_message_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor', 'manager' ],
			array_fill_keys( self::$twilio_message_capabilities, true )
		);
	}

	/**
	 * Add dashboard capabilities
	 */
	private static function add_dashboard_capabilities() {
		self::add_capabilities_to_roles(
			[ 'administrator', 'editor', 'manager' ],
			array_fill_keys( self::$dashboard_capabilities, true )
		);
	}

	/**
	 * Add capabilities to roles
	 *
	 * @param array $roles
	 * @param array $capabilities
	 */
	public static function add_capabilities_to_roles( array $roles, array $capabilities ) {
		foreach ( $roles as $roleName ) {
			$role = get_role( $roleName );
			if ( ! $role instanceof WP_Role ) {
				continue;
			}

			foreach ( $capabilities as $cap => $grant ) {
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap, (bool) $grant );
				}
			}
		}
	}
}
