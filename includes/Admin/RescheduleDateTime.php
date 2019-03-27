<?php

namespace Stackonet\Admin;

use Stackonet\Integrations\Twilio;
use Stackonet\Models\Settings;
use Stackonet\RescheduleAdminEmail;
use Stackonet\RescheduleCustomerEmail;

defined( 'ABSPATH' ) or exit;


class RescheduleDateTime {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;
	private static $timezone_string;
	private static $gmt_offset;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$timezone_string = get_option( 'timezone_string' );
			self::$gmt_offset      = get_option( 'gmt_offset' );

			add_action( 'add_meta_boxes', [ self::$instance, 'add_meta_boxes' ] );
			add_action( 'save_post_shop_order', [ self::$instance, 'save_date_time' ] );
		}

		return self::$instance;
	}

	/**
	 * Add metabox
	 */
	public function add_meta_boxes() {
		add_meta_box( 're_schedule_date_time', 'Re-Schedule Date',
			[ $this, 're_schedule_date_time_callback' ], 'shop_order', 'side', 'low' );
	}

	/**
	 * Meta box callback
	 *
	 * @param \WP_Post $post
	 *
	 * @throws \Exception
	 */
	public function re_schedule_date_time_callback( $post ) {
		$service_date = get_post_meta( $post->ID, '_preferred_service_date', true );
		$time_range   = get_post_meta( $post->ID, '_preferred_service_time_range', true );

		$dateTime = new \DateTime( $service_date );

		// Receive mail come as utc time, not need to set timezone
		if ( is_numeric( self::$gmt_offset ) && self::$gmt_offset ) {
			$dateTime->setTimezone( new \DateTimeZone( self::$gmt_offset ) );
		} elseif ( self::isValidTimezone( self::$timezone_string ) ) {
			$dateTime->setTimezone( new \DateTimeZone( self::$timezone_string ) );
		}

		$date = $dateTime->format( get_option( 'date_format' ) );

		$dateRanges = Settings::get_service_dates_ranges();

		$start = date( 'Y-m-d', time() ) . ' 00:00:01';
		$end   = date( 'Y-m-d', time() ) . ' 24:00:00';
		$date1 = new \DateTime( $start );
		$date2 = new \DateTime( $end );

		$interval = new \DateInterval( 'PT1H' );
		/** @var \DateTime[] $period */
		$period = new \DatePeriod( $date1, $interval, $date2 );

		$_times = [];
		foreach ( $period as $day ) {
			$_h1 = $day->format( 'ha' );
			$day->modify( '+ 1 hour' );
			$_times[] = sprintf( '%s - %s', $_h1, $day->format( 'ha' ) );
		}

		$_reschedule_date = get_post_meta( $post->ID, '_reschedule_date_time', true );
		$_reschedule_date = is_array( $_reschedule_date ) ? $_reschedule_date : [];
		$_total_schedule  = count( $_reschedule_date );

		?>
		<p>
			<strong>Preferred Date & Time: </strong><br>
			<?php echo $date . ', ' . $time_range; ?>
		</p>
		<hr>
		<?php
		if ( $_total_schedule ) {
			echo '<p>';
			echo '<strong>Re-Schedule Date & Time: </strong><br>';
			foreach ( $_reschedule_date as $index => $item ) {
				if ( $index !== ( $_total_schedule - 1 ) ) {
					echo '<span><del>' . $item['date'] . ', ' . $item['time'] . '</del></span>' . '<br>';
				} else {
					echo '<span>' . $item['date'] . ', ' . $item['time'] . '</span>' . '<br>';
				}
			}
			echo '</p>';
		}
		?>
		<p>
			<label for="_reschedule_date">Date: </label>
			<select id="_reschedule_date" name="_reschedule_date" class="widefat">
				<?php
				echo '<option value="">Choose Date</option>';
				foreach ( $dateRanges as $date_range ) {
					$disabled = $date_range['holiday'] ? 'disabled' : '';
					$label    = $date_range['day'] . ', ' . date( get_option( 'date_format' ), strtotime( $date_range['date'] ) );
					echo '<option value="' . $date_range['date'] . '" ' . $disabled . '>' . $label . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="_reschedule_time_range">Time Range: </label>
			<select id="_reschedule_time_range" name="_reschedule_time_range" class="widefat">
				<?php
				echo '<option value="">Choose Time Range</option>';
				foreach ( $_times as $time ) {
					echo '<option value="' . $time . '">' . $time . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<button class="button widefat">Re-Schedule Date & Time</button>
		</p>
		<?php
	}

	/**
	 * Save re-schedule date and time
	 *
	 * @param int $post_id
	 */
	public function save_date_time( $post_id ) {
		$date       = isset( $_POST['_reschedule_date'] ) ? $_POST['_reschedule_date'] : null;
		$time_range = isset( $_POST['_reschedule_time_range'] ) ? $_POST['_reschedule_time_range'] : null;

		if ( empty( $date ) || empty( $time_range ) ) {
			return;
		}

		$_reschedule_date = get_post_meta( $post_id, '_reschedule_date_time', true );
		$_reschedule_date = is_array( $_reschedule_date ) ? $_reschedule_date : [];

		$data = [ 'date' => $date, 'time' => $time_range, 'user' => get_current_user_id() ];

		$_reschedule_date[] = $data;

		update_post_meta( $post_id, '_reschedule_date_time', $_reschedule_date );

		$this->send_sms( $post_id, $data );
		$this->send_email( $post_id, $data );
	}

	/**
	 * Check if a string is a valid timezone
	 *
	 * @param string $timezone
	 *
	 * @return bool
	 */
	public static function isValidTimezone( $timezone ) {
		return in_array( $timezone, timezone_identifiers_list() );
	}

	/**
	 * Send SMS to customer and admin
	 *
	 * @param int $post_id
	 * @param array $data
	 */
	private function send_sms( $post_id, array $data ) {
		$order  = wc_get_order( $post_id );
		$twilio = new Twilio();
		$twilio->send_reschedule_mail( $order );
	}

	/**
	 * Send email to customer and admin
	 *
	 * @param int $post_id
	 * @param array $data
	 */
	private function send_email( $post_id, array $data ) {
		$order = wc_get_order( $post_id );
		$email = wc()->mailer()->get_emails();
		/** @var RescheduleAdminEmail $admin_email */
		$admin_email = $email['admin_reschedule_order'];
		/** @var RescheduleCustomerEmail $user_email */
		$user_email = $email['customer_reschedule_order'];

		$admin_email->trigger( $post_id, $order );
		$user_email->trigger( $post_id, $order );
	}
}
