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
		wp_enqueue_script( 'stackonet-repair-services-frontend' );
		wp_enqueue_style( 'stackonet-repair-services-frontend' );
	}

	/**
	 * display services
	 */
	public function repair_services() {
		echo '<script>window.Stackonet = ' . wp_json_encode( self::temp_data() ) . '</script>';
		echo '<div id="stackonet_repair_services"></div>';
		include STACKONET_REPAIR_SERVICES_PATH . "/assets/img/frontend-icons.svg";
		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );
	}

	public function map_script() {
		$map_src = add_query_arg( array(
			'key'       => Settings::get_map_api_key(),
			'libraries' => 'places'
		), 'https://maps.googleapis.com/maps/api/js' );
		echo '<script src="' . $map_src . '"></script>';
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public static function temp_data() {
		$data = [];

		$data['devices']     = Device::get_devices();
		$data["serviceArea"] = ServiceArea::get_zip_codes();

		$days     = [];
		$date     = new \DateTime();
		$timezone = get_option( 'timezone_string' );
		if ( in_array( $timezone, \DateTimeZone::listIdentifiers() ) ) {
			$date->setTimezone( new \DateTimeZone( $timezone ) );
		}
		$period = new \DatePeriod( $date, new \DateInterval( 'P1D' ), 9 );
		foreach ( $period as $day ) {
			$days[] = $day->format( 'Y-m-d' );
		}

		$data['dateRanges'] = $days;
		$data['timeRanges'] = [
			'9am - 10am',
			'10am - 11am',
			'11am - 12pm',
			'12pm - 1pm',
			'1pm - 2pm',
			'2pm - 3pm',
			'3pm - 4pm',
			'4pm - 5pm',
			'5pm - 6pm',
			'6pm - 7pm',
			'7pm - 8pm',
			'8pm - 9pm',
			'9pm - 10pm',
		];

		return $data;
	}
}
