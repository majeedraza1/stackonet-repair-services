<?php

namespace Stackonet;

defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_loaded', [ self::$instance, 'register' ] );
		}

		return self::$instance;
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
			$version   = isset( $script['version'] ) ? $script['version'] : STACKONET_REPAIR_SERVICES_VERSION;
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$version = time();
			}
			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps    = isset( $style['deps'] ) ? $style['deps'] : false;
			$version = isset( $style['version'] ) ? $style['version'] : STACKONET_REPAIR_SERVICES_VERSION;
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$version = time();
			}
			wp_register_style( $handle, $style['src'], $deps, $version );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts() {
		$scripts = [
			'stackonet-vendors'                  => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/vendors.js',
				'in_footer' => true,
			],
			'material-design-lite'               => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/mdl.js',
				'deps'      => [ 'stackonet-vendors' ],
				'in_footer' => true
			],
			'stackonet-repair-services-frontend' => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/frontend.js',
				'deps'      => [ 'jquery', 'stackonet-vendors', 'material-design-lite' ],
				'in_footer' => true
			],
			'stackonet-frontend-dashboard'       => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/frontend-dashboard.js',
				'deps'      => [ 'jquery', 'stackonet-vendors', 'material-design-lite' ],
				'in_footer' => true
			],
			'stackonet-my-account'               => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/my-account.js',
				'deps'      => [ 'jquery', 'stackonet-vendors', 'material-design-lite' ],
				'in_footer' => true
			],
			'stackonet-rent-center'              => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/rent-center.js',
				'deps'      => [ 'jquery', 'stackonet-vendors', 'material-design-lite' ],
				'in_footer' => true
			],
			'stackonet-repair-services-admin'    => [
				'src'       => STACKONET_REPAIR_SERVICES_ASSETS . '/js/admin.js',
				'deps'      => [ 'jquery', 'wp-color-picker', 'stackonet-vendors', 'material-design-lite' ],
				'in_footer' => true
			],
		];

		return $scripts;
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {
		$styles = [
			'material-design-lite'               => [
				'src' => STACKONET_REPAIR_SERVICES_ASSETS . '/css/mdl.css'
			],
			'stackonet-vendors'                  => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/vendors.css',
				'deps' => [ 'material-design-lite' ],
			],
			'stackonet-repair-services-frontend' => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/frontend.css',
				'deps' => [ 'stackonet-vendors' ],
			],
			'stackonet-frontend-dashboard'       => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/frontend-dashboard.css',
				'deps' => [ 'stackonet-vendors' ],
			],
			'stackonet-repair-services-admin'    => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/admin.css',
				'deps' => [ 'wp-color-picker', 'stackonet-vendors' ],
			],
			'stackonet-my-account'               => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/my-account.css',
				'deps' => [ 'stackonet-vendors' ],
			],
			'stackonet-rent-center'              => [
				'src'  => STACKONET_REPAIR_SERVICES_ASSETS . '/css/rent-center.css',
				'deps' => [ 'stackonet-vendors' ],
			],
		];

		return $styles;
	}
}
