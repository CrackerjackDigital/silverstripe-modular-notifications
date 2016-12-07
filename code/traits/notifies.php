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
	 * Return the current config.notifies_enabled status or set it and return the previous state.
	 * @param bool $enable
	 * @return bool current or previous state if setting new one
	 */
	public static function notifies_enabled($enable = null) {
		$old = (bool)\Config::inst()->get(get_called_class(), 'notifies_enabled');
		if (is_bool($enable)) {
			\Config::inst()->update(get_called_class(), 'notifies_enabled', $enable);
		}
		return $old;
	}

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
		if (static::notifies_enabled()) {
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
		} else {
			$this->debugger()->warn('notifies is not enabled on ' . get_class($this));
		}
	}

}