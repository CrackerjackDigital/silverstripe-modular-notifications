<?php
namespace Modular\Services;

use Modular\Extensions\Service\EmailNotification;
use Modular\Extensions\Service\Enqueue;
use Modular\Interfaces\Notification;
use Modular\Service;
use Modular\Traits\bitfield;
use Modular\Traits\debugging;
use Modular\Traits\emailer;
use Modular\Traits\enabler;
use Modular\Traits\trackable;

class Notifications extends Service {
	use emailer;
	use trackable;
	use bitfield;
	use debugging;
	use enabler;

	const ServiceName = 'NotificationService';

	// options passed to request will override these options, so e.g. if called via notifies trait then
	// options set on the exhibitor will override these options
	private static $options = \Modular\Models\Notification::DefaultNotificationOptions;

	/**
	 * If options indicates immediate, then send immediately, otherwise enqueue the email.
	 *
	 * @param                              $serviceName
	 * @param \Modular\Models\Notification $notification
	 * @param null                         $options use these instead of config.options if not null
	 * @return array
	 */
	public static function request($serviceName, $notification, $options = null) {
		if (static::enabled()) {
			$options = is_null( $options ) ? static::config()->get( 'options' ) : $options;

			if ( static::testbits( $options, Notification::NotifyImmediate ) ) {
				// send notification immediately
				return parent::request( EmailNotification::class, $notification, $options );
			} else {
				// queue the notification
				return parent::request( Enqueue::class, $notification, $options );
			}
		}
	}

	/**
	 * Get the implementor to do something.
	 *
	 * @param array|\ArrayAccess $params e.g. to merge into fields or configure service execution
	 * @param string             $resultMessage
	 *
	 * @return mixed
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		// TODO: Implement execute() method.
	}
}