<?php


namespace Stackonet\Integrations;


use DialogContactForm\Abstracts\Action;
use DialogContactForm\Supports\Config;
use Exception;
use Stackonet\Supports\Logger;

class ContactFormToSupportTicket extends Action {
	/**
	 * UserRegistration constructor.
	 */
	public function __construct() {
		$this->priority   = 20;
		$this->id         = 'support_ticket';
		$this->title      = __( 'Contact Form To Support Ticket', 'dialog-contact-form' );
		$this->meta_group = 'support_ticket';
		$this->meta_key   = '_action_support_ticket';
		// $this->settings   = array_merge( $this->settings, $this->settings() );
	}

	/**
	 * Process current action
	 *
	 * @param Config $config Contact form configurations
	 * @param array $data User submitted sanitized data
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function process( $config, $data ) {
		$_data = [
			'ticket_subject'  => 'Contact form message',
			'customer_name'   => ! empty( $data['your_name'] ) ? $data['your_name'] : '',
			'customer_email'  => ! empty( $data['email'] ) ? $data['email'] : '',
			'user_type'       => 'guest',
			'ticket_category' => get_option( 'wpsc_default_contact_form_ticket_category' ),
		];

		ob_start();
		?>
		<table style="width: 100%;">
			<?php foreach ( $data as $key => $value ) { ?>
				<tr>
					<td><?php echo ucwords( str_replace( '_', ' ', $key ) ); ?>:</td>
					<td><?php echo $value; ?></td>
				</tr>
			<?php } ?>
		</table>
		<?php
		$_content = ob_get_clean();

		( new SupportTicket )->create_support_ticket( $_data, $_content );
	}

	/**
	 * Get action description
	 *
	 * @return string
	 */
	public function getDescription() {
		$html = '<p class="description">';
		$html .= esc_html__( 'No settings are available for this action.', 'dialog-contact-form' );
		$html .= '</p>';

		return $html;
	}
}
