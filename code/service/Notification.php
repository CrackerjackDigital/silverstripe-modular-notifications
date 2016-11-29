<?php
namespace Modular\Services\Notifications;

use Modular\emailer;
use Modular\Fields\QueueStatus;
use Modular\Fields\SentDate;
use Modular\Models\Notification;
use Modular\Service;
use Modular\trackable;

class NotificationService extends Service {
	use emailer;
	use trackable;

	const ServiceName = 'NotificationService';

	private static $pause_seconds = 2;

	public function sendQueuedEmails() {
		$this->trackable_start(__METHOD__);
		$sent = 0;

		do {
			/** @var Notification $notification */
			$notification = Notification::get()->filter([
				QueueStatus::field_name() => QueueStatus::StatusQueued,
				SentDate::field_name()    => '',
			])->sort('Created asc')->first();

			if ($notification) {
				$notification->update([
					QueueStatus::field_name() => QueueStatus::StatusProcessing,
				])->write();

				$subject = $notification->getSubject();

				try {

					$result = $this->emailer_send(
						$notification->getFrom(),
						$notification->getTo(),
						$subject,
						$notification->getBody(),
						$notification->TemplateName
					);

					$notification->update([
						QueueStatus::field_name() => $result ? QueueStatus::StatusCompleted : QueueStatus::StatusFailed,
						SentDate::field_name()    => $result ? date('Y-m-d h:i:s') : '',
					])->write();

					$this->debug_trace("Sent email with subject '$subject'");

				} catch (\Exception $e) {
					$this->debug_error("Failed to send queued notification with subject '$subject'");

					$notification->update([
						QueueStatus::field_name() => QueueStatus::StatusFailed,
					])->write();

				}
				$sent++;
			}
			sleep($this->config()->get('pause_seconds'));

		} while ($notification);

		$this->trackable_end("Sent '$sent' emails");

	}
}