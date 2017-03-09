<?php
namespace Modular\Services;

use Modular\Extensions\Service\EmailNotification;
use Modular\Extensions\Service\Enqueue;
use Modular\Interfaces\Notification;
use Modular\Models\Notification as NotificationModel;
use Modular\Traits\bitfield;
use Modular\Traits\emailer;
use Modular\Traits\trackable;

class Notifications extends Service {
	use emailer;
	use trackable;
	use bitfield;

	const ServiceName = 'NotificationService';

	// options passed to request will override these options, so e.g. if called via notifies trait then
	// options set on the exhibitor will override these options
	private static $options = NotificationModel::DefaultNotificationOptions;

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
}