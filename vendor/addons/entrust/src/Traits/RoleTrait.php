<?php

namespace Addons\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Cache;

trait RoleTrait
{
	//Big block of caching functionality.
	public function cachedPermissions()
	{
		$cacheKey = 'entrust_permissions_for_role_'.$this->getKey();
		return Cache::remember($cacheKey, config('cache.ttl'), function () {
			return $this->perms;
		});
	}

	public static function bootRoleTrait()
	{
		static::saved(function($item){
			Cache::forget('entrust_permissions_for_role_'.$item->getKey());
		});
		static::deleting(function($role) {
			if (!method_exists(config('entrust.role'), 'bootSoftDeletes')) {
				$role->users()->sync([]);
				$role->perms()->sync([]);
			}

			return true;
		});
		static::deleted(function($item){
			Cache::forget('entrust_permissions_for_role_'.$item->getKey());
		});
		if (method_exists(static::class, 'restored'))
			static::restored(function($item){
				Cache::forget('entrust_permissions_for_role_'.$item->getKey());
			});
	}

	/**
	 * Many-to-Many relations with the user model.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users()
	{
		return $this->belongsToMany(config('auth.providers.users.model'), config('entrust.role_user_table'), config('entrust.role_foreign_key'),config('entrust.user_foreign_key'));
	   // return $this->belongsToMany(config('auth.providers.users.model'), config('entrust.role_user_table'));
	}

	/**
	 * Many-to-Many relations with the permission model.
	 * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function perms()
	{
		return $this->belongsToMany(config('entrust.permission'), config('entrust.permission_role_table'), config('entrust.role_foreign_key'), config('entrust.permission_foreign_key'));
	}

	/**
	 * Checks if the role has a permission by its name.
	 *
	 * @param string|array $name       Permission name or array of permission names.
	 * @param bool         $requireAll All permissions in the array are required.
	 *
	 * @return bool
	 */
	public function hasPermission($name, $requireAll = false)
	{
		if (is_array($name)) {
			foreach ($name as $permissionName) {
				$hasPermission = $this->hasPermission($permissionName);

				if ($hasPermission && !$requireAll) {
					return true;
				} elseif (!$hasPermission && $requireAll) {
					return false;
				}
			}

			// If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
			// If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
			// Return the value of $requireAll;
			return $requireAll;
		} else {
			foreach ($this->cachedPermissions() as $permission) {
				if ($permission->name == $name) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Save the inputted permissions.
	 *
	 * @param mixed $inputPermissions
	 *
	 * @return void
	 */
	public function savePermissions($inputPermissions)
	{
		if (!empty($inputPermissions)) {
			$this->perms()->sync($inputPermissions);
		} else {
			$this->perms()->detach();
		}
	}

	/**
	 * Attach permission to current role.
	 *
	 * @param object|array $permission
	 *
	 * @return void
	 */
	public function attachPermission($permission)
	{
		if (is_object($permission)) {
			$permission = $permission->getKey();
		}

		if (is_array($permission)) {
			$permission = $permission['id'];
		}

		$this->perms()->attach($permission);
	}

	/**
	 * Detach permission from current role.
	 *
	 * @param object|array $permission
	 *
	 * @return void
	 */
	public function detachPermission($permission)
	{
		if (is_object($permission))
			$permission = $permission->getKey();

		if (is_array($permission))
			$permission = $permission['id'];

		$this->perms()->detach($permission);
	}

	/**
	 * Attach multiple permissions to current role.
	 *
	 * @param mixed $permissions
	 *
	 * @return void
	 */
	public function attachPermissions($permissions)
	{
		foreach ($permissions as $permission) {
			$this->attachPermission($permission);
		}
	}

	/**
	 * Detach multiple permissions from current role
	 *
	 * @param mixed $permissions
	 *
	 * @return void
	 */
	public function detachPermissions($permissions)
	{
		foreach ($permissions as $permission) {
			$this->detachPermission($permission);
		}
	}
}
