<?php

namespace Addons\Entrust;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Illuminate\Support\Facades\Facade as Base;

class Facade extends Base
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'entrust';
    }
}
