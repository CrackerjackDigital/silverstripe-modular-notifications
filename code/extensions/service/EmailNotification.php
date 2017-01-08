<?php
namespace Modular\Extensions\Service;

use Modular\Fields\QueueStatus;
use Modular\Fields\SentDate;
use Modular\Interfaces\Queueable;
use Modular\Models\Notification;
use Modular\Traits\emailer;

class EmailNotification extends ServiceRequest {
	use emailer;
	
	/**
	 * Send the notification by email.
	 *
	 * @param Queueable|Notification $notification
	 * @param null                   $options
	 * @param \Member|null           $requester
	 * @return \Modular\Interfaces\Queueable|\Modular\Model|string
	 * @throws \ValidationException
	 * @throws null
	 */
	protected function service($notification, $options = null, $requester = null) {
		$notification->updateQueueStatus(QueueStatus::StatusProcessing);
		$notification->write();
		
		$result = $this->emailer_send(
			$notification->getFrom(),
			$notification->getTo(),
			$notification->getSubject(),
			$notification->getMessage(),
			$notification->getTemplateName(),
			$notification->getData(),
			$error
		);
		
		if ($result) {
			$notification->updateQueueStatus(
				QueueStatus::StatusCompleted,
				[
					SentDate::single_field_name() => SentDate::now()
				]
			);
			
		} else {
			$notification->updateQueueStatus(
				QueueStatus::StatusFailed,
				[
					'Error' => $error ?: 'Failed to send'
				]
			);
		}
		
		
		$notification->write();
		
		return $result;
	}
}