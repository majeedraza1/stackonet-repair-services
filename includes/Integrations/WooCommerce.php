<?php

namespace Stackonet\Integrations;

use Exception;
use Stackonet\Models\Settings;
use Stackonet\Supports\Utils;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class WooCommerce {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'woocommerce_email_order_details', [ self::$instance, 'add_extra_data' ], 10, 2 );
			add_action( 'woocommerce_email_customer_details', [ self::$instance, 'add_customer_extra_data' ], 99, 2 );

			add_filter( 'woocommerce_email_classes', array( self::$instance, 'email_classes' ) );
		}

		return self::$instance;
	}

	/**
	 * Add extra order data
	 *
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 */
	public function add_extra_data( $order, $sent_to_admin ) {
		if ( $sent_to_admin ) {
			$preferred_date  = $order->get_meta( '_preferred_service_date', true );
			$preferred_time  = $order->get_meta( '_preferred_service_time_range', true );
			$address         = $order->get_address( 'billing' );
			$billing_address = esc_html( $address['address_1'] );

			if ( ! empty( $address['city'] ) ) {
				$billing_address .= ', ' . esc_html( $address['city'] );
			}
			if ( ! empty( $address['state'] ) ) {
				$billing_address .= ', ' . esc_html( $address['state'] );
			}
			if ( ! empty( $address['postcode'] ) ) {
				$billing_address .= ', ' . esc_html( $address['postcode'] );
			}
			if ( ! empty( $address['country'] ) ) {
				$billing_address .= ', ' . esc_html( $address['country'] );
			}


			// Remove name and company before generate the Google Maps URL.
			unset( $address['first_name'], $address['last_name'], $address['company'], $address['email'], $address['phone'] );
			$map_url = 'https://maps.google.com/maps?&q=' . rawurlencode( implode( ', ', $address ) ) . '&z=16';

			echo '<p>';
			echo 'Preferred Data: ' . esc_attr( $preferred_date ) . '<br>';
			echo 'Preferred Time: ' . esc_attr( $preferred_time ) . '<br>';
			echo 'Customer Location: <a href="' . $map_url . '">' . $billing_address . '</a>';
			echo '</p>';
		}
	}

	/**
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 *
	 * @throws Exception
	 */
	public function add_customer_extra_data( $order, $sent_to_admin ) {
		$service_date   = $order->get_meta( '_preferred_service_date', true );
		$time_range     = $order->get_meta( '_preferred_service_time_range', true );
		$requested_time = sprintf( "%s, %s", mysql2date( 'l j M', $service_date ), $time_range );
		$settings       = Settings::get_settings();
		$reschedule_url = Utils::get_reschedule_url( $order );

		if ( ! $sent_to_admin ) { ?>
            <p>Thank you for placing your order with Phone Repairs ASAP. A professional will meet you at</p>
            <p><?php echo $order->get_formatted_billing_address(); ?></p>
            <p><?php echo esc_html( $requested_time ); ?></p>
            <p>You will be notified via text or phone call when we are close to arriving!</p>
            <p>
                If you have any questions please contact us at
				<?php echo esc_html( $settings['support_phone'] ); ?>
                or email us at <?php echo esc_attr( $settings['support_email'] ); ?>
            </p>

            <p>
                If you wish to reschedule appointment Click <a
                        href="<?php echo $reschedule_url; ?>"><?php echo $reschedule_url; ?></a>
            </p>

            <p><?php echo get_bloginfo( 'name', 'display' ) ?></p>
		<?php }
	}

	/**
	 * Add a custom email to the list of emails WooCommerce should load
	 *
	 * @param array $email_classes available email classes
	 *
	 * @return array filtered available email classes
	 */
	public function email_classes( $email_classes ) {
		// $email_classes['NewDeviceRepairsOrderEmail'] = new \Stackonet\NewDeviceRepairsOrderEmail();
		$email_classes['admin_reschedule_order']    = new \Stackonet\RescheduleAdminEmail();
		$email_classes['customer_reschedule_order'] = new \Stackonet\RescheduleCustomerEmail();

		return $email_classes;
	}
}
