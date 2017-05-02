<?php
namespace Modular\Tasks;

use Modular\Exceptions\Exception;
use Modular\Services\Notifications as NotificationService;
use Modular\Task;
use Modular\Traits\emailer;
use Modular\Traits\trackable;

class Notifications extends Task {
	use emailer;
	use trackable;

	private static $confirmation_token = '';


	public function execute($request = [], &$resultMessage = '') {
		$this->trackable_start(__METHOD__);

		if (!$confirmationToken = isset($request['confirm']) ? $request['confirm'] : '') {
			$this->debug_fail(new Exception("No confirmation token provided"));
		}
		if ($confirmationToken != $this->config()->get('confirmation_token')) {
			$this->debug_fail(new Exception("Invalid confirmation token or none configured on task"));
		}
		NotificationService::factory()->sendQueuedEmails();

		$this->trackable_end();
	}

}