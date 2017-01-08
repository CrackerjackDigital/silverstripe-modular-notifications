<?php
namespace Modular;

use Modular\Extensions\Service\EmailNotification;
use Modular\Extensions\Service\Enqueue;
use Modular\Models\Notification;
use Modular\Services\Notification as NotificationService;

trait notifies {
	// if set on the exhibiting class these will be passed through to the Notification service, overriding
	// options configured on there.
	private static $notifies_options = 0;
	
	/**
	 * @param null   $level
	 * @param string $source
	 * @return \Modular\Debugger
	 */
	abstract public function debugger($level = Debugger::LevelFromEnv, $source = '');
	
	/**
	 * Return the current config.notifies_enabled status or set it and return the previous state.
	 *
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
	 * Return config.notifies_options from exhibiting object.
	 * @return mixed
	 */
	public static function notifies_options() {
		return static::config()->get('notifies_options');
	}
	
	/**
	 * Queue a Notification model with passed and derived parameters, the model and eventual sender handles most
	 * of the work dealing with different parameters, options etc.
	 *
	 * @param mixed  $sender         email address, ID of a Member or a Member
	 * @param mixed  $recipients     email address, ID of a Member or a Member (or an array of these for multiple
	 *                               recipients)
	 * @param string $subject        of message
	 * @param string $message        body to send
	 * @param string $templateName   to use for rendering the email
	 * @param array  $data           additional data will be set on the Notification object created (may be passed to
	 *                               email template)
	 * @param null   $serviceOptions to pass to the service which handles the notification
	 * @return \Modular\Interfaces\Notification
	 * @throws \ValidationException
	 */
	protected function notify($sender, $recipients, $subject, $message, $templateName = '', $data = [], $serviceOptions = null) {
		if (static::notifies_enabled()) {
			/** @var Notification $notification */
			$notification = Notification::create();
			
			$notification->setFrom($sender);
			$notification->setTo($recipients);
			$notification->setSubject($subject);
			$notification->setMessage($message);
			$notification->setTemplateName($templateName);
			$notification->setData($data);
			$notification->setOptions($serviceOptions);
			
			if (Notification::NotifyImmediate === ( static::notifies_options() & Notification::NotifyImmediate )) {
				NotificationService::request(EmailNotification::class, $notification, $serviceOptions);
			} else {
				NotificationService::request(Enqueue::class, $notification, $serviceOptions);
			}
			
			return $notification;
		} else {
			$this->debugger()->warn('notifies is not enabled on ' . get_class($this));
		}
	}
	
}