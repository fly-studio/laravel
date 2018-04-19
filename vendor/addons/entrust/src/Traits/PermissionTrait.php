<?php

namespace Addons\Entrust\Traits;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Illuminate\Support\Facades\Config;

trait PermissionTrait
{
    use DynamicUserRelationsCalls;

    /**
     * Boots the permission model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootPermissionTrait()
    {
        static::deleting(function ($permission) {
            if (!method_exists(Config::get('entrust.models.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }
        });

        static::deleting(function ($permission) {
            if (method_exists($permission, 'bootSoftDeletes') && !$permission->forceDeleting) {
                return;
            }

            $permission->roles()->sync([]);

            foreach (array_keys(Config::get('entrust.user_models')) as $key) {
                $permission->$key()->sync([]);
            }
        });
    }

    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('entrust.models.role'),
            Config::get('entrust.tables.permission_role'),
            Config::get('entrust.foreign_keys.permission'),
            Config::get('entrust.foreign_keys.role')
        );
    }

    /**
     * Morph by Many relationship between the permission and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('entrust.user_models')[$relationship],
            'user',
            Config::get('entrust.tables.permission_user'),
            Config::get('entrust.foreign_keys.permission'),
            Config::get('entrust.foreign_keys.user')
        );
    }
}
