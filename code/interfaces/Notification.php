<?php
namespace Modular\Interfaces;

interface Notification extends \DataObjectInterface {
	const NotifyImmediate = 1;
	const NotifyEnqueue   = 2;
	const NotifyEmail     = 4;

	const DefaultNotificationOptions = 7;  // immediate, enqueue and email

	public function setFrom($from);

	public function getFrom();

	public function setTo($to);

	public function getTo();

	public function setSubject($subject);

	public function getSubject();

	public function setMessage($message);

	public function getMessage();

	/**
	 * @param      $options
	 * @return $this
	 */
	public function setOptions($options);

	public function getOptions();

	public function getTemplateName();

	public function setTemplateName($template);

	public function getData();

	public function setData($data);

	// should really be in DataObjectInterface?
	/**
	 * See DataObject.update(...)
	 * @param array $data
	 * @return $this
	 */
	public function update($data);

	// should really be in DataObjectInterface?
	/**
	 * See DataObject.write(...)
	 * @param bool $showDebug
	 * @param bool $forceInsert
	 * @param bool $forceWrite
	 * @param bool $writeComponents
	 * @return int|null
	 */
	public function write($showDebug = false, $forceInsert = false, $forceWrite = false, $writeComponents = false);

}