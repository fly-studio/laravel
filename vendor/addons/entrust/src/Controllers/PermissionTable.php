<?php

namespace Addons\Entrust\Controllers;

use Illuminate\Support\Facades\Auth;

class PermissionTable {

	protected $permissionTable = null;

	public function __construct(array $permissionTable)
	{
		$this->permissionTable = $permissionTable;
	}

	public function getUser(bool $strict = false)
	{
		return Auth::check() ? Auth::user() : ($strict ? false : Auth::getProvider()->createModel());
	}

	public function checkMethodPermission(string $method)
	{
		$permissionTable = $this->permissionTable;
		if (empty($permissionTable)) return true; // 权限表为空，放行

		$method = strtolower($method);
		!isset($permissionTable[$method]) && $method = '*';

		return !isset($permissionTable[$method]) ? true : $this->checkUserPermission($permissionTable[$method]);
	}

	/**
	 * @example $this->checkUserPermission($perm1, $perm2);
	 * @example $this->checkUserPermission($perm1, $perm2, false);
	 *
	 * @param  [string[]] $permissions 	need matched permission
	 * @param  [bool] $requireAll [true] all permissions must be matched
	 * @return [bool]
	 */
	public function checkUserPermission(...$permissions)
	{
		$user = $this->getUser();

		$requireAll = true;
		if (func_num_args() >= 2 && is_bool(func_get_arg(func_num_args()-1))) //last parameter is bool
			$requireAll = array_pop($permissions);

		return $user->can($permissions, null, $requireAll);
	}

	/**
	 * @example $this->checkUserRole($perm1, $perm2);
	 * @example $this->checkUserRole($perm1, $perm2, false);
	 *
	 * @param  [string[]] $permissions 	need matched permission
	 * @param  [bool] $requireAll [true] all permissions must be matched
	 * @return [bool]
	 */
	public function checkUserRole(...$roles)
	{
		$user = $this->getUser(true);
		if (empty($user)) return false;

		$requireAll = true;
		if (func_num_args() >= 2 && is_bool(func_get_arg(func_num_args()-1))) //last parameter is bool
			$requireAll = array_pop($roles);

		return $user->hasRole($roles, null, $requireAll);
	}

	public static function make(array $permissions)
	{
		$table = [];
		foreach($permissions as $k => $v)
		{
			if (is_numeric($k))
				$table += [
					'index' => $v.'.view',
					'show' => $v.'.view',
					'data' => $v.'.view',
					'export' => $v.'.export',
					'print' => $v.'.export',
					'edit' => $v.'.edit',
					'update' => $v.'.edit',
					'create' => $v.'.create',
					'store' => $v.'.create',
					'destroy' => $v.'.destroy',
				];
			else
				foreach(explode(',', $k) as $key)
					$table[strtolower($key)] = $v;
		}

		return new static($table);
	}
}
