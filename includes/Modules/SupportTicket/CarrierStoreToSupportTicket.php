<?php

namespace Stackonet\Modules\SupportTicket;

use Exception;
use Stackonet\Models\CarrierStore;

defined( 'ABSPATH' ) or exit;

class CarrierStoreToSupportTicket {

	/**
	 * Process appointment to support ticket conversion
	 *
	 * @param CarrierStore $carrier_store
	 *
	 * @throws Exception
	 */
	public static function process( CarrierStore $carrier_store ) {
		$issues = array_column( $carrier_store->get( 'device_issues' ), 'title' );
		$_model = $carrier_store->get( 'model' );
		$model  = ( 'high' == $_model ) ? 'High end model' : 'Low end model';
		$phone  = $carrier_store->get( 'phone' );
		$phone  = '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
		$rows   = [
			[ 'label' => 'Carrier Store ID', 'value' => $carrier_store->get( 'id' ) ],
			[ 'label' => 'Brand', 'value' => $carrier_store->get( 'brand' ) ],
			[ 'label' => 'Gadget', 'value' => $carrier_store->get( 'gadget' ) ],
			[ 'label' => 'Model', 'value' => $model ],
			[ 'label' => 'Tips Amount', 'value' => wc_price( $carrier_store->get( 'tips_amount' ) ) ],
			[ 'label' => 'Name', 'value' => $carrier_store->get( 'name' ) ],
			[ 'label' => 'Store', 'value' => $carrier_store->get( 'store' ) ],
			[ 'label' => 'Industry', 'value' => $carrier_store->get( 'industry' ) ],
			[ 'label' => 'Email', 'value' => $carrier_store->get( 'email' ) ],
			[ 'label' => 'Phone', 'value' => $phone ],
			[ 'label' => 'Full Address', 'value' => $carrier_store->get( 'full_address' ) ],
		];
		$images = $carrier_store->get_images();
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

		$address = $carrier_store->get( 'address' );

		$_data = [
			'ticket_subject'  => 'Carrier Store - #' . $carrier_store->get( 'id' ),
			'customer_name'   => $carrier_store->get( 'name' ),
			'customer_email'  => $carrier_store->get( 'email' ),
			'customer_phone'  => $carrier_store->get( 'phone' ),
			'city'            => ! empty( $address['city']['long_name'] ) ? $address['city']['long_name'] : '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'carrier_store_default_category' ),
		];

		( new SupportTicket )->create_support_ticket( $_data, $content );
	}
}
