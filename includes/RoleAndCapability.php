<?php

namespace Stackonet;

use WP_Role;

defined( 'ABSPATH' ) || exit;

class RoleAndCapability {

	/**
	 * Support Ticket capabilities
	 *
	 * @var array
	 */
	protected static $ticket_capabilities = [
		'manage_tickets',
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
		'read_survey',
		'add_survey',
		'delete_survey',
		'manage_survey',
	];

	/**
	 * Device capabilities
	 *
	 * @var array
	 */
	protected static $device_capabilities = [
		'read_phone',
		'add_phone',
		'delete_phone',
		'manage_phones',
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
			array_fill_keys( self::$ticket_capabilities, true )
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
