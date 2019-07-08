<?php

namespace Stackonet\Frontend;

use Stackonet\Models\Settings;
use Stackonet\Modules\SupportTicket\SupportAgent;
use Stackonet\Modules\SupportTicket\SupportTicket;
use Stackonet\Modules\SupportTicket\TicketCategory;
use Stackonet\Modules\SupportTicket\TicketPriority;
use Stackonet\Modules\SupportTicket\TicketStatus;
use WP_Post;

class DashboardPage {
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

			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_dashboard_scripts' ] );
			add_shortcode( 'stackonet_frontend_dashboard', [ self::$instance, 'frontend_dashboard' ] );
		}

		return self::$instance;
	}

	/**
	 * Load frontend dashboard script
	 */
	public function load_dashboard_scripts() {
		global $post;
		if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'stackonet_frontend_dashboard' ) ) {
			wp_enqueue_style( 'stackonet-frontend-dashboard' );
			wp_enqueue_script( 'stackonet-frontend-dashboard' );
		}
	}

	/**
	 * Frontend dashboard
	 *
	 * @return string
	 */
	public function frontend_dashboard() {
		$data             = [];
		$custom_logo_id   = get_theme_mod( 'custom_logo' );
		$_logo_url        = wp_get_attachment_image_src( $custom_logo_id );
		$data['logo_url'] = '';
		if ( isset( $_logo_url[0] ) && filter_var( $_logo_url[0], FILTER_VALIDATE_URL ) ) {
			$data['logo_url'] = $_logo_url[0];
		}

		$user = wp_get_current_user();

		if ( ! $user->exists() ) {
			wp_localize_script( 'stackonet-frontend-dashboard', 'SupportTickets', $data );

			return '<div id="stackonet_repair_services_dashboard_login"></div>';
		}

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
		$data['order_statuses']    = wc_get_order_statuses();

		wp_localize_script( 'stackonet-frontend-dashboard', 'SupportTickets', $data );
		wp_localize_script( 'stackonet-frontend-dashboard', 'StackonetDashboard', [
			'menuItems' => $this->menu()
		] );

		add_action( 'wp_footer', array( $this, 'map_script' ), 1 );
		add_action( 'wp_footer', [ $this, 'tinymce_script' ], 9 );

		return '<div id="stackonet_repair_services_dashboard"></div>';
	}

	/**
	 * Dashboard Menu
	 *
	 * @return array
	 */
	public function menu() {
		$menu = [];

		if ( current_user_can( 'read_reports' ) ) {
			$menu[] = [ 'router' => '/report', 'label' => 'Dashboard' ];
		}

		if ( current_user_can( 'read_tickets' ) ) {
			$menu[] = [ 'router' => '/ticket', 'label' => 'Support' ];
			// $menu[] = [ 'router' => '/map', 'label' => 'Map' ];
		}

		if ( current_user_can( 'read_surveys' ) ) {
			$menu[] = [ 'router' => '/survey', 'label' => 'Survey' ];
			$menu[] = [ 'router' => '/carrier-stores', 'label' => 'Carrier Stores' ];
			$menu[] = [ 'router' => '/lead', 'label' => 'Lead' ];
		}

		if ( current_user_can( 'read_twilio_messages' ) ) {
			$menu[] = [ 'router' => '/sms', 'label' => 'SMS' ];
		}

		if ( current_user_can( 'read_checkout_analysis_records' ) ) {
			$menu[] = [ 'router' => '/checkout-analysis', 'label' => 'Checkout Analysis' ];
		}

		if ( current_user_can( 'manage_options' ) ) {
			$menu[] = [
				'type'   => 'link',
				'label'  => 'Google Analytics',
				'url'    => 'https://analytics.google.com/analytics/web/',
				'target' => '_blank'
			];
		}

		return $menu;
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
	 * TinyMce script
	 */
	public function tinymce_script() {
		echo '<script type="text/javascript" src="' . includes_url( 'js/tinymce/tinymce.min.js' ) . '"></script>';
	}
}
