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

trait TeamTrait
{
    use DynamicUserRelationsCalls;

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('entrust.user_models')[$relationship],
            'user',
            Config::get('entrust.tables.role_user'),
            Config::get('entrust.foreign_keys.team'),
            Config::get('entrust.foreign_keys.user')
        );
    }

    /**
     * Boots the team model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the team model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootTeamTrait()
    {
        static::deleting(function ($team) {
            if (method_exists($team, 'bootSoftDeletes') && !$team->forceDeleting) {
                return;
            }

            foreach (array_keys(Config::get('entrust.user_models')) as $key) {
                $team->$key()->sync([]);
            }
        });
    }
}
