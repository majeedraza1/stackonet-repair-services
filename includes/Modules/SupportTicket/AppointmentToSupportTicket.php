<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\Models\Appointment;

defined( 'ABSPATH' ) or exit;

class AppointmentToSupportTicket {

	/**
	 * Process appointment to support ticket conversion
	 *
	 * @param Appointment $appointment
	 *
	 * @throws Exception
	 */
	public static function process( Appointment $appointment ) {
		$issues = array_column( $appointment->get( 'device_issues' ), 'title' );
		$phone  = $appointment->get( 'phone' );
		$phone  = '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
		$rows   = [
			[ 'label' => 'Spot Appointment ID', 'value' => $appointment->get( 'id' ) ],
			[ 'label' => 'Gadget', 'value' => $appointment->get( 'gadget' ) ],
			[ 'label' => 'Device', 'value' => $appointment->get( 'device' ) ],
			[ 'label' => 'Device Model', 'value' => $appointment->get( 'device_model' ) ],
			[ 'label' => 'Appointment Date', 'value' => $appointment->get( 'appointment_date' ) ],
			[ 'label' => 'Appointment Time', 'value' => $appointment->get( 'appointment_time' ) ],
			[ 'label' => 'Email', 'value' => $appointment->get( 'email' ) ],
			[ 'label' => 'Phone', 'value' => $phone ],
			[ 'label' => 'Store Name', 'value' => $appointment->get( 'store_name' ) ],
			[ 'label' => 'Full Address', 'value' => $appointment->get( 'full_address' ) ],
			[ 'label' => 'Note', 'value' => $appointment->get( 'note' ) ],
			[ 'label' => 'Device Issues', 'value' => implode( ', ', $issues ) ],
		];
		$images = $appointment->get_images();
		ob_start();
		?>
		<table class="table--support-order">
			<?php foreach ( $rows as $row ) { ?>
				<tr>
					<td><?php echo $row['label']; ?>:</td>
					<td><?php echo $row['value']; ?></td>
				</tr>
			<?php } ?>
			<?php
			if ( count( $images ) ) {
				echo '<td>Image(s):</td>';
				echo '<td>';
				foreach ( $images as $index => $image ) {
					if ( $index !== 0 ) {
						echo '<br>';
					}
					echo '<img src="' . $image['full']['src'] . '"  alt="' . $image['title'] . '"/>';
				}
				echo '</td>';
			}
			?>
		</table>
		<?php
		$content = ob_get_clean();

		$address = $appointment->get( 'address' );

		$_data = [
			'ticket_subject'  => 'Appointment - ' . implode( ', ', $issues ),
			'customer_name'   => $appointment->get( 'store_name' ),
			'customer_email'  => $appointment->get( 'email' ),
			'customer_phone'  => $appointment->get( 'phone' ),
			'city'            => ! empty( $address['city']['long_name'] ) ? $address['city']['long_name'] : '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'wpsc_default_spot_appointment_category' ),
		];

		( new SupportTicket )->create_support_ticket( $_data, $content );
	}

	/**
	 * @throws Exception
	 */
	public static function current_appointment_to_support_ticket() {
		$option = get_option( 'init_appointment_to_support_ticket' );
		if ( version_compare( $option, '1.0.1', '<' ) ) {
			$appoints = ( new Appointment() )->find( [ 'per_page' => 100, ] );
			foreach ( $appoints as $appoint ) {
				static::process( $appoint );
			}
			update_option( 'init_appointment_to_support_ticket', '1.0.1' );
		}
	}
}
