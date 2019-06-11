<?php

namespace Stackonet;

use Exception;
use Stackonet\Models\Device;
use Stackonet\Models\ServiceArea;
use Stackonet\Models\Settings;
use Stackonet\Models\Testimonial;
use Stackonet\Modules\SupportTicket\SupportAgent;
use Stackonet\Modules\SupportTicket\SupportTicket;
use Stackonet\Modules\SupportTicket\TicketCategory;
use Stackonet\Modules\SupportTicket\TicketPriority;
use Stackonet\Modules\SupportTicket\TicketStatus;
use WC_Order;
use WP_Post;

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
			add_shortcode( 'stackonet_repair_service_pricing', [ self::$instance, 'repair_services_pricing' ] );
			add_shortcode( 'stackonet_testimonial_form', [ self::$instance, 'testimonial_form' ] );
			add_shortcode( 'stackonet_manager_registration_form', [ self::$instance, 'manager_registration_form' ] );
			add_shortcode( 'stackonet_client_testimonial', [ self::$instance, 'client_testimonial' ] );
			add_shortcode( 'stackonet_reschedule_order', [ self::$instance, 'reschedule_order' ] );
			add_shortcode( 'stackonet_rent_a_center', [ self::$instance, 'rent_a_center' ] );
			add_shortcode( 'stackonet_survey_form', [ self::$instance, 'survey_form' ] );
			add_shortcode( 'stackonet_spot_appointment', [ self::$instance, 'spot_appointment' ] );
			add_shortcode( 'stackonet_become_technician', [ self::$instance, 'become_technician' ] );
			add_shortcode( 'stackonet_support_ticket', [ self::$instance, 'support_ticket' ] );
			add_shortcode( 'stackonet_checkout_analysis', [ self::$instance, 'checkout_analysis' ] );
			add_shortcode( 'stackonet_frontend_dashboard', [ self::$instance, 'frontend_dashboard' ] );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_scripts' ] );
		}

		return self::$instance;
	}

	/**
	 * Load frontend scripts
	 */
	public function load_scripts() {
		wp_enqueue_style( 'stackonet-repair-services-frontend' );
		wp_localize_script( 'jquery', 'PhoneRepairs', self::dynamic_data() );

		if ( $this->should_load_scripts() ) {
			wp_enqueue_script( 'stackonet-repair-services-frontend' );
			wp_localize_script( 'stackonet-repair-services-frontend', 'Stackonet', self::service_data() );
		}
	}

	/**
	 * Check if scripts should load
	 *
	 * @return bool
	 */
	public function should_load_scripts() {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}

		$shortcodes = [
			'stackonet_repair_service',
			'stackonet_testimonial_form',
			'stackonet_client_testimonial',
			'stackonet_repair_service_pricing',
			'stackonet_reschedule_order',
			'stackonet_manager_registration_form',
			'stackonet_survey_form',
			'stackonet_spot_appointment',
			'stackonet_become_technician',
			'stackonet_rent_a_center',
			'stackonet_support_ticket',
			'stackonet_support_ticket_form',
			'stackonet_checkout_analysis',
			'stackonet_frontend_dashboard',
		];

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		foreach ( $shortcodes as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checkout analysis
	 *
	 * @return string
	 */
	public function checkout_analysis() {
		if ( ! current_user_can( 'add_survey' ) ) {
			return '<div>Please login to view this page content.</div>';
		}

		return '<div id="stackonet_checkout_analysis"></div>';
	}

	/**
	 * Frontend dashboard
	 *
	 * @return string
	 */
	public function frontend_dashboard() {
		if ( ! current_user_can( 'add_survey' ) ) {
			$login_url = wp_login_url( get_permalink() );

			return '<div>Please <a href="' . $login_url . '">login</a> to view this page content.</div>';
		}

		$data          = [];
		$supportTicket = new SupportTicket();

		$_statuses        = $supportTicket->get_ticket_statuses_terms();
		$data['statuses'] = [ [ 'key' => 'all', 'label' => 'All Statuses', 'count' => 0, 'active' => true ] ];
		foreach ( $_statuses as $status ) {
			$data['statuses'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}
		$data['statuses'][] = [ 'key' => 'trash', 'label' => 'Trash', 'count' => 0, 'active' => false ];


		$_categories        = $supportTicket->get_categories_terms();
		$data['categories'] = [ [ 'key' => 'all', 'label' => 'All Categories', 'count' => 0, 'active' => true ] ];
		foreach ( $_categories as $status ) {
			$data['categories'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$_priorities        = $supportTicket->get_priorities_terms();
		$data['priorities'] = [ [ 'key' => 'all', 'label' => 'All Priorities', 'count' => 0, 'active' => true ] ];
		foreach ( $_priorities as $status ) {
			$data['priorities'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$data['count_trash'] = $supportTicket->count_trash_records();

		$data['ticket_categories'] = TicketCategory::get_all();
		$data['ticket_statuses']   = TicketStatus::get_all();
		$data['ticket_priorities'] = TicketPriority::get_all();
		$data['support_agents']    = SupportAgent::get_all();

		$user           = wp_get_current_user();
		$data['user']   = [
			'display_name' => $user->display_name,
			'user_email'   => $user->user_email,
			'avatar_url'   => get_avatar_url( $user->user_email ),
		];
		$data['cities'] = $supportTicket->find_all_cities();

		$data['search_categories'] = (array) get_option( 'stackonet_ticket_search_categories' );

		wp_localize_script( 'stackonet-repair-services-frontend', 'SupportTickets', $data );

		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );
		add_action( 'wp_footer', [ $this, 'tinymce_script' ], 9 );

		return '<div id="stackonet_repair_services_dashboard"></div>';
	}

	/**
	 * Rent a center shortcode content
	 *
	 * @return string
	 */
	public function rent_a_center() {
		return '<div id="stackonet_rent_a_center"></div>';
	}

	/**
	 * Survey form
	 *
	 * @return string
	 */
	public function survey_form() {
		if ( ! current_user_can( 'add_survey' ) ) {
			return '<div>Please login to view this form.</div>';
		}

		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );

		return '<div id="stackonet_survey_form"></div>';
	}

	/**
	 * Survey form
	 *
	 * @return string
	 */
	public function spot_appointment() {
		if ( ! current_user_can( 'add_survey' ) ) {
			return '<div>Please login to view this form.</div>';
		}

		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );

		return '<div id="stackonet_spot_appointment"></div>';
	}

	/**
	 * Become a technician form
	 *
	 * @return string
	 */
	public function become_technician() {
		return '<div id="stackonet_become_technician"></div>';
	}

	/**
	 * Become a technician form
	 *
	 * @return string
	 */
	public function support_ticket() {
		if ( ! current_user_can( 'read' ) ) {
			$login_url = wp_login_url( get_permalink() );
			$html      = '<p>You need to <a href="' . esc_url( $login_url ) . '">Log in</a>.</p>';

			return $html;
		}
		$data          = [];
		$supportTicket = new SupportTicket();

		$_statuses        = $supportTicket->get_ticket_statuses_terms();
		$data['statuses'] = [ [ 'key' => 'all', 'label' => 'All Statuses', 'count' => 0, 'active' => true ] ];
		foreach ( $_statuses as $status ) {
			$data['statuses'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}
		$data['statuses'][] = [ 'key' => 'trash', 'label' => 'Trash', 'count' => 0, 'active' => false ];


		$_categories        = $supportTicket->get_categories_terms();
		$data['categories'] = [ [ 'key' => 'all', 'label' => 'All Categories', 'count' => 0, 'active' => true ] ];
		foreach ( $_categories as $status ) {
			$data['categories'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$_priorities        = $supportTicket->get_priorities_terms();
		$data['priorities'] = [ [ 'key' => 'all', 'label' => 'All Priorities', 'count' => 0, 'active' => true ] ];
		foreach ( $_priorities as $status ) {
			$data['priorities'][] = [ 'key' => $status->term_id, 'label' => $status->name, 'count' => 0, ];
		}

		$data['count_trash'] = $supportTicket->count_trash_records();

		$data['ticket_categories'] = TicketCategory::get_all();
		$data['ticket_statuses']   = TicketStatus::get_all();
		$data['ticket_priorities'] = TicketPriority::get_all();
		$data['support_agents']    = SupportAgent::get_all();

		$user           = wp_get_current_user();
		$data['user']   = [
			'display_name' => $user->display_name,
			'user_email'   => $user->user_email,
		];
		$data['cities'] = $supportTicket->find_all_cities();

		$data['search_categories'] = (array) get_option( 'stackonet_ticket_search_categories' );

		wp_localize_script( 'stackonet-repair-services-frontend', 'SupportTickets', $data );
		add_action( 'wp_footer', [ $this, 'tinymce_script' ], 9 );

		return '<div id="stackonet_support_ticket"></div>';
	}


	public function tinymce_script() {
		echo '<script type="text/javascript" src="' . includes_url( 'js/tinymce/tinymce.min.js' ) . '"></script>';
	}

	/**
	 * Show reschedule order frontend
	 */
	public function reschedule_order() {
		$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
		$token    = isset( $_GET['token'] ) ? wp_strip_all_tags( $_GET['token'] ) : '';

		if ( empty( $order_id ) || empty( $token ) ) {
			return '<p>Link expired.</p>';
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return '<p>Link expired.</p>';
		}

		if ( $token != $order->get_meta( '_reschedule_hash', true ) ) {
			return '<p>Link expired.</p>';
		}

		$_reschedule_date = $order->get_meta( '_reschedule_date_time', true );
		$_reschedule_date = is_array( $_reschedule_date ) ? $_reschedule_date : [];

		if ( empty( $_reschedule_date ) ) {
			$service_date = $order->get_meta( '_preferred_service_date', true );
			$time_range   = $order->get_meta( '_preferred_service_time_range', true );

			$_reschedule_date = [ [ 'date' => $service_date, 'time' => $time_range ], ];
		}

		$data = [
			'order_id'   => $order_id,
			'token'      => $token,
			'reschedule' => $_reschedule_date,
		];
		$html = '<script>window.RescheduleData = ' . wp_json_encode( $data ) . '</script>';
		$html .= '<div id="stackonet_reschedule_order"></div>';

		return $html;
	}

	/**
	 * display services
	 *
	 * @param array $attrs
	 *
	 * @return false|string
	 */
	public function repair_services( $attrs ) {
		$attrs = shortcode_atts( array( 'group' => '', ), $attrs, 'stackonet_repair_service' );
		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );

		ob_start();
		echo '<div class="stackonet_repair_services_container" data-group="' . esc_attr( $attrs['group'] ) . '">';
		echo '<div id="stackonet_repair_services"></div>';
		echo '</div>';
		include STACKONET_REPAIR_SERVICES_PATH . "/assets/img/frontend-icons.svg";

		return ob_get_clean();
	}

	/**
	 * Service pricing page
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function repair_services_pricing( $atts ) {
		$atts = shortcode_atts( array( 'page_id' => 0, ), $atts, 'stackonet_repair_service_pricing' );

		$cta_url = '';
		if ( ! empty( $atts['page_id'] ) ) {
			$cta_url = get_permalink( $atts['page_id'] );
		}

		return '<div class="stackonet_pricing_container" data-cta_url="' . esc_attr( $cta_url ) . '"><div id="stackonet_repair_services_pricing"></div></div>';
	}

	/**
	 * Client Testimonial
	 */
	public function client_testimonial() {
		$testimonial = new Testimonial();
		/** @var Testimonial[] $testimonials */
		$testimonials = $testimonial->find( [ 'status' => 'accept' ] );
		ob_start();
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
							<div class="mdl-card__supporting-text">
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
		return ob_get_clean();
	}

	/**
	 * Testimonial form
	 */
	public function testimonial_form() {
		return '<div id="stackonet_testimonial_form"></div>';
	}

	/**
	 * Manager registration form
	 */
	public function manager_registration_form() {
		return '<div id="stackonet_manager_registration_form"></div>';
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
	 * Plugin core dynamic data
	 *
	 * @return array
	 */
	public static function dynamic_data() {
		global $is_iphone;

		$data = [
			'ajaxurl'     => admin_url( 'admin-ajax.php' ),
			'site_url'    => site_url(),
			'home_url'    => home_url(),
			'login_url'   => wp_login_url(),
			'logout_url'  => wp_logout_url( home_url() ),
			'myaccount'   => wc_get_page_permalink( 'myaccount' ),
			'token'       => wp_create_nonce( 'confirm_appointment' ),
			'rest_root'   => esc_url_raw( rest_url( 'stackonet/v1' ) ),
			'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
			'is_iphone'   => $is_iphone,
			'map_api_key' => Settings::get_map_api_key(),
		];

		return $data;
	}

	/**
	 * Get service data
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function service_data() {
		$data = self::dynamic_data();

		$data['devices']         = Device::get_devices();
		$data["serviceArea"]     = ServiceArea::get_zip_codes();
		$data['dateRanges']      = Settings::get_service_dates_ranges();
		$data['timeRanges']      = Settings::get_service_times_ranges();
		$data['todayTimeRanges'] = Settings::get_today_times_ranges();

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
