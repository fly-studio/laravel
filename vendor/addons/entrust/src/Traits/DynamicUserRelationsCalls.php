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

trait DynamicUserRelationsCalls
{
    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getUsersRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        return $this->getRelationshipFromMethod($key);
    }

    /**
     * Dynamically retrieve the relationship value with the possible user models.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, array_keys(Config::get('entrust.user_models')))) {
            return $this->getUsersRelationValue($key);
        }

        return parent::__get($key);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, array_keys(Config::get('entrust.user_models')))) {
            return $this->getMorphByUserRelation($method);
        }

        return parent::__call($method, $parameters);
    }
}
