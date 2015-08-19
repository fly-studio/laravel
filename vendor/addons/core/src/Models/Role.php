<?php

namespace Addons\Core\Models;


use Zizaco\Entrust\EntrustRole;
class Role extends EntrustRole
{
	const ADMIN = 'admin';
	const MANGER = 'manger';
	const OWNER = 'owner';
	const LEADER = 'leader';
	const VIEWER = 'view';

	//不能批量赋值
	protected $guarded = [];

	public function getRoles()
	{
		$roles = [];
		$_roles = $this->orderBy('id', 'ASC')->get();
		foreach ($_roles as $role) {
			$roles[$role['name']] = $role->toArray() + ['perms' =>[]];
			$perms = $role->perms;
			foreach($perms as $perm)
				$roles[($role['name'])]['perms'][] = $perm['name'];
		}
		return $roles;
	}
}
