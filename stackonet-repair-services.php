<?php
/**
 * Plugin Name: Stackonet Toolkit for Phone Repairs ASAP
 * Description: Stackonet Toolkit for Phone Repairs ASAP
 * Version: 1.0.2
 * Author: Stackonet Services (Pvt.) Ltd.
 * Author URI: http://www.stackonet.com/
 * Requires at least: 4.9
 * Tested up to: 5.1
 * Text Domain: stackonet-repair-services
 */

defined( 'ABSPATH' ) || exit;

final class Stackonet_Repair_Services {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name = 'stackonet-repair-services';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.0.2';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.3';

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 * @throws Exception
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// define constants
			self::$instance->define_constants();

			// Check if PHP version is supported for our plugin
			if ( ! self::$instance->is_supported_php() ) {
				register_activation_hook( __FILE__, array( self::$instance, 'auto_deactivate' ) );
				add_action( 'admin_notices', array( self::$instance, 'php_version_notice' ) );

				return self::$instance;
			}

			// Include Classes
			self::$instance->include_classes();

			// initialize the classes
			add_action( 'plugins_loaded', array( self::$instance, 'init_classes' ) );

			// Load plugin textdomain
			add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ) );

			add_action( 'phone_repairs_asap_activation', [ self::$instance, 'add_custom_role' ] );

			// Register plugin activation activity
			register_activation_hook( __FILE__, array( self::$instance, 'activation' ) );
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 */
	private function define_constants() {
		define( 'STACKONET_REPAIR_SERVICES', $this->plugin_name );
		define( 'STACKONET_REPAIR_SERVICES_VERSION', $this->version );
		define( 'STACKONET_REPAIR_SERVICES_FILE', __FILE__ );
		define( 'STACKONET_REPAIR_SERVICES_PATH', dirname( STACKONET_REPAIR_SERVICES_FILE ) );
		define( 'STACKONET_REPAIR_SERVICES_INCLUDES', STACKONET_REPAIR_SERVICES_PATH . '/includes' );
		define( 'STACKONET_REPAIR_SERVICES_URL', plugins_url( '', STACKONET_REPAIR_SERVICES_FILE ) );
		define( 'STACKONET_REPAIR_SERVICES_ASSETS', STACKONET_REPAIR_SERVICES_URL . '/assets' );
	}


	/**
	 * Include classes
	 *
	 * @throws Exception
	 */
	private function include_classes() {
		spl_autoload_register( function ( $className ) {
			if ( class_exists( $className ) ) {
				return;
			}

			// project-specific namespace prefix
			$prefix = 'Stackonet\\';

			// base directory for the namespace prefix
			$base_dir = STACKONET_REPAIR_SERVICES_INCLUDES . DIRECTORY_SEPARATOR;

			// does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $className, $len ) !== 0 ) {
				// no, move to the next registered autoloader
				return;
			}

			// get the relative class name
			$relative_class = substr( $className, $len );

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// if the file exists, require it
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		} );
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 * @throws Exception
	 */
	public function init_classes() {
		$this->container['assets']         = Stackonet\Assets::init();
		$this->container['twilio']         = Stackonet\Integrations\Twilio::init();
		$this->container['woocommerce']    = Stackonet\Integrations\WooCommerce::init();
		$this->container['reschedule']     = new Stackonet\Models\Reschedule();
		$this->container['order_reminder'] = Stackonet\Models\OrderReminder::init();

		$this->container['my_account'] = Stackonet\Modules\MyAccount\MyAccount::init();

		if ( $this->is_request( 'admin' ) ) {
			$this->container['reschedule-date-time'] = Stackonet\Admin\RescheduleDateTime::init();
			$this->container['admin']                = Stackonet\Admin\Admin::init();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend']              = Stackonet\Frontend::init();
			$this->container['rest-testimonial']      = Stackonet\REST\TestimonialController::init();
			$this->container['rest-unsupported-area'] = Stackonet\REST\UnsupportedAreaController::init();
			$this->container['rest-reschedule-order'] = Stackonet\REST\OrderRescheduleController::init();
			$this->container['rest-devices']          = Stackonet\REST\DeviceController::init();
			$this->container['rest-issues']           = Stackonet\REST\IssueController::init();
			$this->container['rest-phones']           = Stackonet\REST\PhoneController::init();
			$this->container['rest-track-status']     = Stackonet\REST\TrackStatusController::init();
			$this->container['rest-survey']           = Stackonet\REST\SurveyController::init();
		}

		if ( $this->is_request( 'ajax' ) ) {
			$this->container['ajax'] = Stackonet\Ajax::init();
		}
	}

	/**
	 * Async reschedule request
	 *
	 * @return Stackonet\Models\Reschedule
	 */
	public function async_reschedule() {
		return $this->container['reschedule'];
	}

	/**
	 * Async reschedule request
	 *
	 * @return Stackonet\Models\OrderReminder
	 */
	public function order_reminder() {
		return $this->container['order_reminder'];
	}

	/**
	 * Create tables on plugin activation
	 */
	public function activation() {
		$area = new Stackonet\Models\UnsupportedArea();
		$area->create_table();
		$testimonial = new Stackonet\Models\Testimonial();
		$testimonial->create_table();
		$testimonial = new Stackonet\Models\Phone();
		$testimonial->create_table();
		$survey = new Stackonet\Models\Survey();
		$survey->create_table();

		do_action( 'phone_repairs_asap_activation' );
	}

	/**
	 * Add custom role
	 */
	public function add_custom_role() {
		if ( ! get_role( 'manager' ) ) {
			$caps = [
				'read_phone'    => true,
				'add_phone'     => true,
				'delete_phone'  => true,
				'manage_phones' => true,
			];

			add_role( 'manager', 'Manager', array_merge( $caps, [ 'read' => true ] ) );

			$admin_role  = get_role( 'administrator' );
			$editor_role = get_role( 'editor' );

			foreach ( $caps as $cap => $grant ) {
				$admin_role->add_cap( $cap, $grant );
				$editor_role->add_cap( $cap, $grant );
			}
		}
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_plugin_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->plugin_name );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->plugin_name, $locale );

		// Setup paths to current locale file
		$mofile_global = WP_LANG_DIR . '/stackonet-repair-services/' . $mofile;

		// Look in global /wp-content/languages/dialog-contact-form folder
		if ( file_exists( $mofile_global ) ) {
			load_textdomain( $this->plugin_name, $mofile_global );
		}
	}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	public function php_version_notice() {

		if ( $this->is_supported_php() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$error = __( 'Your installed PHP Version is: ', 'vue-wp-starter' ) . PHP_VERSION . '. ';
		$error .= sprintf( __( 'The Dialog Contact Form plugin requires PHP version %s or greater.',
			'vue-wp-starter' ), $this->min_php );
		?>
		<div class="error">
			<p><?php printf( $error ); ?></p>
		</div>
		<?php
	}

	/**
	 * Bail out if the php version is lower than
	 *
	 * @return void
	 */
	public function auto_deactivate() {
		if ( $this->is_supported_php() ) {
			return;
		}

		deactivate_plugins( plugin_basename( __FILE__ ) );

		$error = '<h1>' . __( 'An Error Occurred', 'vue-wp-starter' ) . '</h1>';
		$error .= '<h2>' . __( 'Your installed PHP Version is: ', 'vue-wp-starter' ) . PHP_VERSION . '</h2>';
		$error .= '<p>' . sprintf( __( 'The Dialog Contact Form plugin requires PHP version %s or greater',
				'vue-wp-starter' ), $this->min_php ) . '</p>';
		$error .= '<p>' . sprintf( __( 'The version of your PHP is %s unsupported and old %s. ',
				'vue-wp-starter' ),
				'<a href="http://php.net/supported-versions.php" target="_blank"><strong>',
				'</strong></a>'
			);
		$error .= __( 'You should update your PHP software or contact your host regarding this matter.',
				'vue-wp-starter' ) . '</p>';

		wp_die( $error, __( 'Plugin Activation Error', 'vue-wp-starter' ), array( 'back_link' => true ) );
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, rest, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();

			case 'ajax' :
				return defined( 'DOING_AJAX' );

			case 'rest' :
				return defined( 'REST_REQUEST' );

			case 'cron' :
				return defined( 'DOING_CRON' );

			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Check if the PHP version is supported
	 *
	 * @param null $min_php
	 *
	 * @return bool
	 */
	private function is_supported_php( $min_php = null ) {

		$min_php = $min_php ? $min_php : $this->min_php;

		if ( version_compare( PHP_VERSION, $min_php, '<=' ) ) {
			return false;
		}

		return true;
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 * @throws Exception
 */
function stackonet_repair_services() {
	return Stackonet_Repair_Services::init();
}

stackonet_repair_services();
