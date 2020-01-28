<?php


namespace Stackonet\Integrations;


class MercantileTheme {

	/**
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_filter( 'mercantile_sidebar_layout_options', [ self::$instance, 'sidebar_layout_options' ] );
			add_action( 'wp_head', [ self::$instance, 'custom_style' ] );
		}

		return self::$instance;
	}

	public function custom_style() {
		?>
		<style>
			body.full-screen .site-content {
				max-width: 100% !important;
				margin: auto !important;
			}

			@media (min-width: 1200px) {
				body.full-screen .container {
					width: 100%;
				}
				body.full-screen #primary {
					width: 100%;
				}
			}
		</style>
		<?php
	}

	public function sidebar_layout_options( $options ) {
		$options['full-screen'] = array(
			'value'     => 'full-screen',
			'thumbnail' => get_template_directory_uri() . '/acmethemes/images/no-sidebar.jpg'
		);

		return $options;
	}
}
