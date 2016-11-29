<?php
namespace Modular\Build;

use Modular\Models\Build;

class Notifications extends Build {
	const DefaultAdminPermissionCode = 'CAN_ADMIN_NOTIFICATIONS';
	const DefaultAdminGroupCode      = 'admin-notifications';

	private static $permission_code = self::DefaultAdminPermissionCode;

	private static $group_code = self::DefaultAdminGroupCode;

	/**
	 * Adds permissions to the database on /dev/build if they don't exist.
	 */
	public function requireDefaultRecords() {
		$permissionCode = $this->config()->get('permission_code');

		if (!$permission = \Permission::get()->filter('Code', $permissionCode)->first()) {
			$permission = \Permission::create([
				'Code' => $permissionCode,
				'Type' => 1,
			]);
			$permission->write();
			\DB::alteration_message("Added permission '$permissionCode'", 'created');
		}

		$groupCode = $this->config()->get('group_code');

		if (!$group = \Group::get()->filter('Code', $groupCode)->first()) {
			$group = \Group::create([
				'Title'       => 'Notification Administrators',
				'Description' => 'Member of this group can administer notifications',
				'Code'        => $groupCode,
			]);
			$group->Permissions()->add($permission);
			$group->write();
			\DB::alteration_message("Created group '$groupCode' with permission '$permissionCode'", 'created');
		}
	}
}