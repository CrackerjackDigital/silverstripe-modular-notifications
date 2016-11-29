<?php
namespace Modular\Models;

use Modular\Model;

/**
 * Notification
 *
 * @package Modular\Models
 * @property string Subject
 * @property string Body
 * @property string From
 * @property string To
 * @property string SenderEmail
 * @property string RecipientEmail
 * @property string Title
 * @property string Content
 * @property string TemplateName
 * @property string TemplateData
 * @method \DataList|\ArrayList Recipients()
 */
class Notification extends Model {
	const NotifyImmediate = 1;
	const NotifyQueued    = 2;

	const DefaultNotificationMode = 3;  // immediate and queued

	private static $notification_mode = self::DefaultNotificationMode;

	public static function notification_mode() {
		return static::config()->get('notification_mode');
	}
	/**
	 * Return a list of Email addresses from related Recipients
	 *
	 * @return array
	 */
	public function getTo() {
		return $this->Recipients()->column('Email');
	}

	/**
	 * Sets list of Recipients from provided email addresses.
	 *
	 * @param array|string|\SS_List $to single or array of Email addresses or objects with an 'Email' field
	 * @return $this
	 */
	public function setTo($to) {
		if ($to instanceof \SS_List) {
			$to = $to->column('Email');
		} else if (!is_array($to)) {
			$to = [$to];
		}
		$to = array_filter($to);
		$this->Recipients()->removeAll();

		foreach ($to as $address) {
			$recipient = new Recipient([
				'Member' => \Member::get()->filter('Email', $address)->first(),
				'Email'  => $address,
			]);
			$recipient->write();
			$this->Recipients()->add($recipient);
		}
		return $this;
	}

	public function getFrom() {
		return $this->SenderEmail;
	}

	public function setFrom($from) {
		$this->SenderEmail = $from;
		return $this;
	}

	public function getSubject() {
		return $this->Title;
	}

	public function setSubject($subject) {
		$this->Title = $subject;
		return $this;
	}

	public function getBody() {
		return $this->Content;
	}

	public function setBody($body) {
		$this->Body = $body;
		return $this;
	}

	/**
	 * Returns decoded data
	 *
	 * @return mixed
	 */
	public function getData() {
		return json_decode($this->TemplateData);
	}

	/**
	 * Encode and store passed raw data.
	 *
	 * @param $rawData
	 * @return $this
	 */
	public function setData($rawData) {
		$this->TemplateData = json_encode($rawData);
		return $this;
	}

}