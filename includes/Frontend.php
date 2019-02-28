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
		$testimonials = $testimonial->find( [ 'status' => 'accept' ] );
		?>
		<div class="fp-tns-slider-outer arrows-visible-hover">
			<div class="fp-tns-slider-controls">
	            <span class="prev" data-controls="prev">
	                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32">
	                    <path
		                    d="M12.3 17.71l6.486 6.486c0.39 0.39 1.024 0.39 1.414 0s0.39-1.024 0-1.414l-5.782-5.782 5.782-5.782c0.39-0.39 0.39-1.024 0-1.414s-1.024-0.39-1.414 0l-6.486 6.486c-0.196 0.196-0.292 0.452-0.292 0.71s0.096 0.514 0.292 0.71z"></path>
	                </svg>
	            </span>
				<span class="next" data-controls="next">
	                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32">
	                    <path
		                    d="M13.8 24.196c0.39 0.39 1.024 0.39 1.414 0l6.486-6.486c0.196-0.196 0.294-0.454 0.292-0.71 0-0.258-0.096-0.514-0.292-0.71l-6.486-6.486c-0.39-0.39-1.024-0.39-1.414 0s-0.39 1.024 0 1.414l5.782 5.782-5.782 5.782c-0.39 0.39-0.39 1.024 0 1.414z"></path>
	                </svg>
	            </span>
			</div>
			<div class="client-testimonial fp-tns-slider">
				<?php foreach ( $testimonials as $testimonial ) { ?>
					<div class="client-testimonial-item item">
						<div class="demo-card-wide mdl-card mdl-shadow--2dp">
							<div class="client-testimonial-avatar">
								<div class="client-testimonial-avatar-image">
									<?php
									if ( ! empty( $testimonial->get( 'image_id' ) ) ) {
										$image = wp_get_attachment_image_src( $testimonial->get( 'image_id' ), 'thumbnail' );
										echo '<img src="' . esc_url( $image[0] ) . '">';
									}
									?>
								</div>
							</div>
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
				<?php } ?>
			</div>
		</div>
		<?php
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
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'site_url'   => site_url(),
			'home_url'   => home_url(),
			'token'      => wp_create_nonce( 'confirm_appointment' ),
			'rest_root'  => esc_url_raw( rest_url( 'stackonet/v1' ) ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		];

		$data['devices']     = Device::get_devices();
		$data["serviceArea"] = ServiceArea::get_zip_codes();
		$data['dateRanges']  = Settings::get_service_dates_ranges();
		$data['timeRanges']  = Settings::get_service_times_ranges();

		$data['icons'] = [
			'check'            => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-check.png',
			'checkCircle'      => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-check-circle.png',
			'clock'            => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-clock.png',
			'contact'          => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-contact.png',
			'envelope'         => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-envelope.png',
			'map'              => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-map.png',
			'phone'            => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-phone.png',
			'screenBrokenNo'   => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-screen-broken-no.png',
			'screenBrokenYes'  => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-screen-broken-yes.png',
			'screenMultiIssue' => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-screen-multi-issue.png',
			'unsupportedIssue' => STACKONET_REPAIR_SERVICES_ASSETS . '/img/icon-unsupported-issue.png',
		];

		return $data;
	}
}
