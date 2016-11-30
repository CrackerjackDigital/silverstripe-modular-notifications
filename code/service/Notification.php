<?php
namespace Modular\Services;

use Modular\bitfield;
use Modular\emailer;
use Modular\Exceptions\Exception;
use Modular\Fields\QueueStatus;
use Modular\Fields\SentDate;
use Modular\Service;
use Modular\trackable;

class Notification extends Service {
	use emailer;
	use trackable;
	use bitfield;

	const ServiceName = 'NotificationService';

	private static $pause_seconds = 2;

	private static $options = \Modular\Interfaces\Notification::NotifyImmediate
		| \Modular\Interfaces\Notification::NotifyEnqueue
		| \Modular\Interfaces\Notification::NotifyEmail;

	/**
	 * @param \Modular\Interfaces\Notification $notification
	 * @return \Modular\Interfaces\Notification
	 */
	public function add(\Modular\Interfaces\Notification $notification) {
		$options = $notification->getOptions() ?: $this->config()->get('options');

		if ($this->bitfieldTest($options, \Modular\Interfaces\Notification::NotifyEnqueue)) {
			try {
				$notification->write();
			} catch (\Exception $e) {
				$this->debugger()->error("Failed in notifies.add: " . $e->getMessage());
			}
		}
		if ($this->bitfieldTest($options, \Modular\Interfaces\Notification::NotifyImmediate)) {
			if ($this->bitfieldTest($options, \Modular\Interfaces\Notification::NotifyEmail)) {
				$this->sendByEmail($notification);

				try {
					$notification->write();
				} catch (\Exception $e) {
					$this->debugger()->error("Failed in notifies.add: " . $e->getMessage());
				}
			}
		}
		return $notification;
	}

	/**
	 * Sends a notification by email, ignores the notification's own 'send by email' option will always send if
	 * the service is configured to send emails.
	 *
	 * @param \Modular\Interfaces\Notification $notification
	 * @return \Modular\Interfaces\Notification
	 */
	public function sendByEmail(\Modular\Interfaces\Notification $notification) {
		if ($this->bitfieldTest($this->config()->get('options'), \Modular\Interfaces\Notification::NotifyEmail)) {
			$result = $this->emailer_send(
				$notification->getFrom(),
				$notification->getTo(),
				$notification->getSubject(),
				$notification->getMessage(),
				$notification->getTemplateName(),
				$notification->getData()
			);
		} else {
			$result = false;
		}
		$notification->update([
			QueueStatus::field_name() => $result ? QueueStatus::StatusCompleted : QueueStatus::StatusFailed,
			SentDate::field_name()    => $result ? date('Y-m-d h:i:s') : '',
		])->write();

		return $notification;
	}

	/**
	 * @return int
	 */
	public function processQueue(&$processed = 0, &$failed = 0, &$emailed = 0) {
		$this->trackable_start(__METHOD__);
		$sent = 0;

		do {

			/** @var Notification|\Modular\Interfaces\Notification $notification */
			$notification = \Modular\Models\Notification::create()->filter([
				QueueStatus::field_name() => QueueStatus::StatusQueued,
				SentDate::field_name()    => '',
			])->sort('Created asc')->first();

			if ($notification) {
				$processed++;

				$notification->update([
					QueueStatus::field_name() => QueueStatus::StatusProcessing,
				])->write();

				$subject = $notification->getSubject();

				try {
					if ($this->bitfieldTest($notification->getOptions(), \Modular\Interfaces\Notification::NotifyEmail)) {
						if ($this->sendByEmail($notification)) {
							$this->debug_trace("Sent email with subject '$subject'");
							$emailed++;
						} else {
							throw new Exception("Failed to send email with subject '$subject'");
						}
					}

				} catch (\Exception $e) {
					$failed++;

					$this->debug_error("Failed to send queued notification with subject '$subject'");

					$notification->update([
						QueueStatus::field_name() => QueueStatus::StatusFailed,
					])->write();

				}
			}
			sleep($this->config()->get('pause_seconds'));

		} while ($notification);

		$this->trackable_end("Sent '$sent' emails");
		return $sent;
	}
}