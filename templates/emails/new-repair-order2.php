<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p>Thank you for placing your order with Phone Repairs ASAP. A professional will meet you at</p>
	<p><?php echo $order->get_formatted_billing_address(); ?></p>
	<p><?php echo esc_html( $requested_time ); ?></p>
	<p>You will be notified via text or phone call when we are close to arriving!</p>
	<p>
		If you have any questions please contact us at
		<?php echo esc_html( $settings['support_phone'] ); ?>
		or email us at <?php echo esc_attr( $settings['support_email'] ); ?>
	</p>

	<p><?php echo get_bloginfo( 'name', 'display' ) ?></p>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
