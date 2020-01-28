<?php

namespace Stackonet\Sms;

use Stackonet\Abstracts\BackgroundProcess;
use Stackonet\Integrations\Twilio;

defined( 'ABSPATH' ) || exit;

class BulkSms extends BackgroundProcess {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'bulk_sms_background_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		if ( ! empty( $item['content'] ) && ! empty( $item['number'] ) ) {
			( new Twilio() )->send_sms( $item['number'], $item['content'] );
		}

		return false;
	}
}
