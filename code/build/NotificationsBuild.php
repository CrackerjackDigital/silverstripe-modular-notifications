<?php
use Modular\Extensions\Model\Builder;

/**
 * Add this extension to Modular\Models\Builder model to get notification permissions to build on /dev/build.
 *
 * @package Modular\Build
 */
class NotificationsBuild extends Builder  {
	const DefaultAdminPermissionCode = 'CAN_ADMIN_Notifications';
	const DefaultAdminGroupCode      = 'admin-notifications';
	const DefaultParentGroupCode = 'social';

	private static $parent_group_code = self::DefaultParentGroupCode;

	private static $permission_code = self::DefaultAdminPermissionCode;

	private static $group_code = self::DefaultAdminGroupCode;

	/**
	 * Adds permissions to the database on /dev/build if they don't exist.
	 */
	public function requireDefaultRecords() {
		if ($this->shouldRun()) {
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

			$parent = \Group::get()->filter('Code', $this->config()->get('parent_group_code'))->first();

			if (!$group = \Group::get()->filter('Code', $groupCode)->first()) {
				$group = \Group::create([
					'Title'       => 'Notification Administrators',
					'Synopsis' => 'Member of this group can administer notifications',
					'Code'        => $groupCode,
					'ParentID'    => $parent ? $parent->ID : null,
				]);
				$group->Permissions()->add($permission);
				$group->write();
				\DB::alteration_message("Created group '$groupCode' with permission '$permissionCode'", 'created');
			}
		}
	}
}