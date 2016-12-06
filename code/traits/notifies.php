<?php
namespace Modular;

use Modular\Models\Notification;

trait notifies {
	/**
	 * @param null   $level
	 * @param string $source
	 * @return \Modular\Debugger
	 */
	abstract public function debugger($level = Debugger::LevelFromEnv, $source = '');

	/**
	 * Queue a Notification model with passed and derived parameters.
	 *
	 * @param string $sender
	 * @param string $recipients
	 * @param string $subject
	 * @param string $message
	 * @param string $templateName
	 * @return \Modular\Interfaces\Notification
	 *
	 */
	protected function notify($sender, $recipients, $subject, $message, $templateName = '', $data = [], $options = null) {
		/** @var \Modular\Interfaces\Notification $notification */
		$notification = Notification::create();

		$notification->setFrom($sender);
		$notification->setTo($recipients);
		$notification->setSubject($subject);
		$notification->setMessage($message);
		$notification->setTemplateName($templateName);
		$notification->setData($data);
		$notification->setOptions($options);


		return \Modular\Services\Notification::factory()->add($notification);
	}

}