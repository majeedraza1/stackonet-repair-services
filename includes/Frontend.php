<?php

namespace Stackonet;

defined( 'ABSPATH' ) || exit;

class Frontend {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_shortcode( 'stackonet_repair_service', [ self::$instance, 'repair_services' ] );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_scripts' ] );
		}

		return self::$instance;
	}

	/**
	 * Load frontend scripts
	 */
	public function load_scripts() {
		wp_enqueue_script( 'stackonet-repair-services-frontend' );
		wp_enqueue_style( 'stackonet-repair-services-frontend' );
	}

	/**
	 * display services
	 */
	public function repair_services() {
		echo '<script>window.Stackonet = ' . wp_json_encode( self::temp_data() ) . '</script>';
		echo '<div id="stackonet_repair_services"></div>';
	}

	/**
	 * @return array
	 */
	public static function temp_data() {
		$img  = STACKONET_REPAIR_SERVICES_ASSETS . '/img';
		$data = [];

		$data['devices'] = [
			[ 'title' => 'iPhone', 'image' => $img . '/iphone.png' ],
			[ 'title' => 'Samsung', 'image' => $img . '/samsumg.png' ],
			[ 'title' => 'Google', 'image' => $img . '/google.png' ],
			[ 'title' => 'LG', 'image' => $img . '/lg.png' ],
			[ 'title' => 'iPad', 'image' => $img . '/ipad.png' ],
			[ 'title' => 'motorola', 'image' => $img . '/motorola.svg' ],
			[ 'title' => 'HTC', 'image' => $img . '/htc.png' ],
			[ 'title' => 'Sony', 'image' => $img . '/sony.png' ],
		];

		$data['deviceModel'] = [
			[ 'title' => 'X' ],
			[ 'title' => '8 Plus' ],
			[ 'title' => '8' ],
			[ 'title' => '7 Plus' ],
			[ 'title' => '7' ],
			[ 'title' => '6s Plus' ],
			[ 'title' => '6s' ],
			[ 'title' => 'SE' ],
			[ 'title' => '6 Plus' ],
			[ 'title' => '6' ],
		];

		return $data;
	}
}
