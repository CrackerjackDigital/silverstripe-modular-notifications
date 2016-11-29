<?php
namespace Modular\Tasks;

use Modular\emailer;
use Modular\Exceptions\Exception;
use Modular\Services\Notifications\NotificationService;
use Modular\Task;
use Modular\trackable;

class Notifications extends Task {
	use emailer;
	use trackable;

	private static $confirmation_token = '';

	public function run($request = null) {
		$this->trackable_start(__METHOD__);

		$request = $request ?: \Controller::curr()->getRequest();
		if (!$confirmationToken = !$request->requestVar('confirm')) {
			$this->debug_fail(new Exception("No confirmation token provided"));
		}
		if ($confirmationToken != $this->config()->get('confirmation_token')) {
			$this->debug_fail(new Exception("Invalid confirmation token or none configured on task"));
		}
		NotificationService::factory()->sendQueuedEmails();

		$this->trackable_end();
	}

}