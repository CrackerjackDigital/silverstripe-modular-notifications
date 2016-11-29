<?php
namespace Modular\Models;

use Modular\Model;

/**
 * Recipient class tracks email addresses which may or may not belong to a registered member (who may also be tracked).
 *
 * @package Modular\Models
 * @method \Member Member
 * @property string Email
 */
class Recipient extends Model {

	public function getAddress() {
		$member = $this->Member();
		if ($member && $member->exists()) {
			return $member->Email;
		}
		return $this->Email;
	}
	public function setAddress($address) {
		$this->Email = $address;
		$this->MemberID = \Member::get()->filter('Email', $address)->first();
		return $this;
	}
}