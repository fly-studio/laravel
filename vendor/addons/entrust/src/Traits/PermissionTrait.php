<?php
namespace Addons\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */

trait PermissionTrait
{
	/**
	 * Many-to-Many relations with role model.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(config('entrust.role'), config('entrust.permission_role_table'));
	}

	/**
	 * Boot the permission model
	 * Attach event listener to remove the many-to-many records when trying to delete
	 * Will NOT delete any records if the permission model uses soft deletes.
	 *
	 * @return void|bool
	 */
	public static function bootPermissionTrait()
	{
		static::deleting(function($permission) {
			if (!method_exists(config('entrust.permission'), 'bootSoftDeletes')) {
				$permission->roles()->sync([]);
			}

			return true;
		});
	}
}
