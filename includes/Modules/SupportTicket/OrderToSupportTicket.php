<?php

namespace Stackonet\Modules\SupportTicket;

use DateTime;
use Exception;
use Stackonet\Supports\Utils;
use WC_Order;

defined( 'ABSPATH' ) or exit;

class OrderToSupportTicket {
	/**
	 * Process order to support ticket conversion
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public static function process( WC_Order $order ) {
		$_device_title  = $order->get_meta( '_device_title' );
		$_device_model  = $order->get_meta( '_device_model' );
		$_device_issues = $order->get_meta( '_device_issues' );
		$ticket_subject = $_device_title . ' ' . $_device_model . ' - ' . $order->get_formatted_billing_full_name() . ' - ' . implode( ', ', $_device_issues );
		$ticket_content = self::get_support_ticket_content( $order );

		$ticket_id = ( new SupportTicket() )->create_support_ticket( [
			'ticket_subject'  => $ticket_subject,
			'customer_name'   => $order->get_formatted_billing_full_name(),
			'customer_email'  => $order->get_billing_email(),
			'customer_phone'  => $order->get_billing_phone(),
			'city'            => $order->get_billing_city(),
			'user_type'       => $order->get_customer_id() ? 'user' : 'guest',
			'ticket_category' => get_option( 'wpsc_default_order_ticket_category' ),
		], $ticket_content );

		$order->add_meta_data( '_support_ticket_id', $ticket_id );
		$order->save_meta_data();

		$supportTicket = new SupportTicket();
		if ( $ticket_id ) {
			$supportTicket->update_metadata( $ticket_id, 'created_via', 'order' );
			$supportTicket->update_metadata( $ticket_id, 'belongs_to_id', $order->get_id() );

			add_action( 'stackonet_support_ticket/v1/ticket_created', $ticket_id );
		}
	}

	/**
	 * Get support ticket content for order
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_support_ticket_content( WC_Order $order ) {
		$_device_title = $order->get_meta( '_device_title' );
		$_device_model = $order->get_meta( '_device_model' );
		$_device_color = $order->get_meta( '_device_color' );
		$service_date  = $order->get_meta( '_preferred_service_date' );
		$service_time  = $order->get_meta( '_preferred_service_time_range' );
		$order_url     = add_query_arg( [ 'post' => $order->get_id(), 'action' => 'edit' ], admin_url( 'post.php' ) );

		$_promotion_discount = $order->get_meta( '_promotion_discount' );

		$phone = $order->get_billing_phone();
		$phone = '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';

		$timezone = Utils::get_timezone();
		$dateTime = new DateTime( $service_date, $timezone );
		$date     = $dateTime->format( get_option( 'date_format' ) );

		$address = $order->get_address( 'shipping' );
		unset( $address['first_name'], $address['last_name'], $address['company'] );
		$address = WC()->countries->get_formatted_address( $address, ', ' );

		$issues_description   = $order->get_meta( '_additional_issues_description' );
		$address_instructions = $order->get_meta( '_additional_address_instructions' );
		$formatted_address    = $order->get_meta( '_formatted_address' );
		if ( ! empty( $formatted_address ) ) {
			$address = $formatted_address;
		}

		ob_start();
		?>
		<table class="table--support-order">
			<tr>
				<td>Name:</td>
				<td><strong><?php echo $order->get_formatted_billing_full_name() ?></strong></td>
			</tr>
			<tr>
				<td>Phone:</td>
				<td><strong><?php echo $phone ?></strong></td>
			</tr>
			<tr>
				<td> Customer Address:</td>
				<td>
					<a target="_blank"
					   href="<?php echo $order->get_shipping_address_map_url(); ?>"><?php echo $address; ?></a>
					<?php
					if ( ! empty( $address_instructions ) ) {
						echo '<p>' . $address_instructions . '</p>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td>Preferred Date & Time:</td>
				<td><strong><?php echo $date . ', ' . $service_time; ?></strong></td>
			</tr>
			<tr>
				<td>Order ID:</td>
				<td><strong>#<?php echo $order->get_id() ?></strong></td>
			</tr>
			<tr>
				<td>Device:</td>
				<td>
					<strong>
						<?php echo $_device_title; ?>
						<?php echo $_device_model; ?> (<?php echo $_device_color; ?> )
					</strong>
				</td>
			</tr>
			<tr>
				<td> Order URL:</td>
				<td><a target="_blank" href="<?php echo $order_url; ?>"><strong><?php echo $order_url; ?></strong></a>
				</td>
			</tr>
			<tr>
				<td>Took promotional discount?:</td>
				<td><?php echo $_promotion_discount; ?></td>
			</tr>
			<?php if ( ! empty( $issues_description ) ) { ?>
				<tr>
					<td> Issue Description:</td>
					<td><?php echo $issues_description; ?></td>
				</tr>
			<?php } ?>
		</table>
		<hr>
		<p>Issues:</p>
		<table class="table--issues" style="width: 100%;">
			<tr>
				<th>Issue</th>
				<th>Total</th>
				<th>Tax</th>
			</tr>
			<?php
			$fees   = $order->get_fees();
			$amount = 0;
			foreach ( $fees as $fee ) {
				$amount += floatval( $fee->get_total() );
				?>
				<tr>
					<td><?php echo wp_kses_post( $fee->get_name() ); ?></td>
					<td><?php echo wc_price( $fee->get_total() ); ?></td>
					<td><?php echo wc_price( $fee->get_total_tax() ); ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Subtotal:</td>
				<td><?php echo wc_price( $amount ); ?></td>
			</tr>
			<?php
			$taxes = $order->get_tax_totals();
			foreach ( $taxes as $fee ) {
				?>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo wp_kses_post( $fee->label ); ?></td>
					<td><?php echo wp_kses_post( $fee->formatted_amount ); ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td>&nbsp;</td>
				<td>Total:</td>
				<td><strong><?php echo $order->get_formatted_order_total(); ?></strong></td>
			</tr>
		</table>
		<?php
		return ob_get_clean();
	}
}
