<?php

namespace Addons\Entrust\Contracts;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
interface Ownable
{
    /**
     * Gets the owner key value inside the model or object.
     *
     * @param  mixed  $owner
     * @return mixed
     */
    public function ownerKey($owner);
}
