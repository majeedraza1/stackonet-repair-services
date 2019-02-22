<?php

namespace Stackonet;

use Stackonet\Models\Device;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;
use Stackonet\Models\Testimonial;

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
			add_shortcode( 'stackonet_testimonial_form', [ self::$instance, 'testimonial_form' ] );
			add_shortcode( 'stackonet_client_testimonial', [ self::$instance, 'client_testimonial' ] );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_scripts' ] );
		}

		return self::$instance;
	}

	/**
	 * Load frontend scripts
	 */
	public function load_scripts() {
		if ( $this->should_load_scripts() ) {
			wp_enqueue_script( 'stackonet-repair-services-frontend' );
			wp_enqueue_style( 'stackonet-repair-services-frontend' );

			wp_localize_script( 'stackonet-repair-services-frontend', 'Stackonet', self::service_data() );
		}
	}

	public function should_load_scripts() {
		global $post;
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, 'stackonet_repair_service' ) ) {
			return true;
		}

		if ( has_shortcode( $post->post_content, 'stackonet_testimonial_form' ) ) {
			return true;
		}

		if ( has_shortcode( $post->post_content, 'stackonet_client_testimonial' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * display services
	 */
	public function repair_services() {
//		echo '<script>window.Stackonet = ' . wp_json_encode( self::service_data() ) . '</script>';
		echo '<div id="stackonet_repair_services"></div>';
		include STACKONET_REPAIR_SERVICES_PATH . "/assets/img/frontend-icons.svg";
		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );
	}

	/**
	 * Client Testimonial
	 */
	public function client_testimonial() {
		$testimonial = new Testimonial();
		/** @var Testimonial[] $testimonials */
		$testimonials = $testimonial->find();

		echo '<div class="client-testimonial">';
		foreach ( $testimonials as $testimonial ) { ?>
			<div class="client-testimonial-item">
				<div class="demo-card-wide mdl-card mdl-shadow--2dp">
					<div class="mdl-card__supporting-text">
						<div class="client-testimonial-description">
							<?php echo esc_html( $testimonial->get( 'description' ) ) ?>
						</div>
					</div>
					<div class="mdl-card__actions mdl-card--border">
						<div class="client-name">
							<?php echo esc_html( $testimonial->get( 'name' ) ); ?>
						</div>
						<div class="mdl-layout-spacer"></div>
						<div class="star-rating">
							<?php
							for ( $i = 1; $i <= 5; $i ++ ) {
								$class = ( $testimonial->get( 'rating' ) >= $i ) ? 'star-rating__star is-selected' : 'star-rating__star';
								echo '<label class="' . esc_attr( $class ) . '">★</label>';
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php }
		echo '</div>';
	}

	/**
	 * Testimonial form
	 */
	public function testimonial_form() {
		echo '<div id="stackonet_testimonial_form"></div>';
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
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'site_url' => site_url(),
			'home_url' => home_url(),
			'token'    => wp_create_nonce( 'confirm_appointment' ),
		];

		$data['devices']     = Device::get_devices();
		$data["serviceArea"] = ServiceArea::get_zip_codes();
		$data['dateRanges']  = Settings::get_service_dates_ranges();
		$data['timeRanges']  = Settings::get_service_times_ranges();

		return $data;
	}
}
