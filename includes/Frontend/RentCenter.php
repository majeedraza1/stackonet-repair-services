<?php

namespace Stackonet\Frontend;

class RentCenter {


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

			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_scripts' ] );

			add_shortcode( 'stackonet_rent_a_center', [ self::$instance, 'rent_a_center' ] );
		}

		return self::$instance;
	}

	/**
	 * Load Rent a Center page
	 */
	public function load_scripts() {
		global $post;
		if ( is_page() && has_shortcode( $post->post_content, 'stackonet_rent_a_center' ) ) {
			wp_enqueue_style( 'stackonet-rent-center' );
			wp_enqueue_script( 'stackonet-rent-center' );
		}
	}

	/**
	 * Rent a center shortcode content
	 *
	 * @return string
	 */
	public function rent_a_center() {
		return '<div id="stackonet_rent_a_center"></div>';
	}
}
