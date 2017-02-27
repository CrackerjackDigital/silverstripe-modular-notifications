<?php
namespace Modular\Models;

use Modular\Fields\QueueStatus;
use Modular\Model;
use Modular\Traits\custom_create;

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
 * @property string Data
 * @property string QueueStatus
 * @method \DataList|\ArrayList Recipients()
 */
class Notification extends Model implements \Modular\Interfaces\Notification {
	use custom_create;

	private static $db = [
		'Options' => 'Text',
	    'Data' => 'Text'
	];

	private static $custom_class_name = '';

	/**
	 * @return \Modular\Interfaces\Notification|$this
	 */
	public static function create() {
		return static::custom_create(func_num_args());
	}

	public function getQueueStatus() {
		return $this->QueueStatus;
	}

	/**
	 * Updates the QueueStatus and extraData fields and writes the model.
	 *
	 * @param string $status    one of the StatusABC constants
	 * @param array  $extraData will be updated on the Queueable before writing, e.g. could be 'SentDate' for an email.
	 * @return mixed
	 * @throws \ValidationException
	 */
	public function updateQueueStatus($status, $extraData = []) {
		$this->{QueueStatus::field_name()} = $status;
		$this->update($extraData);
		$this->write();
		return $this;
	}

	public function setOptions($options) {
		$this->Options = $options;
		return $this;
	}

	public function getOptions() {
		return $this->Options;
	}

	/**
	 * Return a list of Email addresses from related Recipients
	 *
	 * @return array
	 */
	public function getTo() {
		return $this->Recipients()->column('Email');
	}

	public function setTemplateName($templateName) {
		$this->TemplateName = $templateName;
		return $this;
	}

	public function getTemplateName() {
		return $this->TemplateName;
	}

	/**
	 * Returns decoded data
	 *
	 * @return mixed
	 */
	public function getData() {
		return json_decode($this->Data);
	}

	/**
	 * Encode and store passed raw data.
	 *
	 * @param $rawData
	 * @return $this
	 */
	public function setData($rawData) {
		$this->Data = json_encode($rawData);
		return $this;
	}

	/**
	 * Sets list of Recipients from provided email addresses.
	 *
	 * @param array|string|\SS_List $to single or array of Email addresses or objects with an 'Email' field
	 * @return $this
	 * @throws \ValidationException
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

	public function getMessage() {
		return $this->Content;
	}

	public function setMessage($message) {
		$this->Content = $message;
		return $this;
	}

}