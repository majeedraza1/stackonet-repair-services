<?php

namespace Stackonet;

use Stackonet\Models\Device;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;

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
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'stackonet_repair_service' ) ) {
			wp_enqueue_script( 'stackonet-repair-services-frontend' );
			wp_enqueue_style( 'stackonet-repair-services-frontend' );
		}
	}

	/**
	 * display services
	 */
	public function repair_services() {
		echo '<script>window.Stackonet = ' . wp_json_encode( self::service_data() ) . '</script>';
		echo '<div id="stackonet_repair_services"></div>';
		include STACKONET_REPAIR_SERVICES_PATH . "/assets/img/frontend-icons.svg";
		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );
	}

	/**
	 * Load google place autocomplete script
	 */
	public function map_script() {
		$map_src = add_query_arg( array(
			'key'       => Settings::get_map_api_key(),
			'libraries' => 'places'
		), 'https://maps.googleapis.com/maps/api/js' );
		echo '<script src="' . $map_src . '"></script>';
	}

	/**
	 * Get service data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function service_data() {
		$data = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'token'   => wp_create_nonce( 'confirm_appointment' ),
		];

		$data['devices']     = Device::get_devices();
		$data["serviceArea"] = ServiceArea::get_zip_codes();
		$data['dateRanges']  = Settings::get_service_dates_ranges();
		$data['timeRanges']  = Settings::get_service_times_ranges();

		return $data;
	}
}
