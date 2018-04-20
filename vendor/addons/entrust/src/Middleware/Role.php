<?php

namespace Addons\Entrust\Middleware;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Closure;

class Role extends Middleware
{
    /**
     * Handle incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure $next
     * @param  string  $roles
     * @param  string|null  $team
     * @param  string|null  $options
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $team = null, $options = '')
    {
        if (!$this->authorization('roles', $roles, $team, $options)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
