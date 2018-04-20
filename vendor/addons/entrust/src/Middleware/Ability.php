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
use Illuminate\Support\Facades\Auth;

class Ability extends Middleware
{

    /**
     * Handle incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure $next
     * @param  string  $roles
     * @param  string  $permissions
     * @param  string|null  $team
     * @param  string|null  $options
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions, $team = null, $options = '')
    {
        list($team, $validateAll, $guard) = $this->assignRealValuesTo($team, $options);

        if (!is_array($roles)) {
            $roles = explode(self::DELIMITER, $roles);
        }

        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if (
            Auth::guard($guard)->guest()
            || !Auth::guard($guard)->user()
                    ->ability($roles, $permissions, $team, [
                        'validate_all' => $validateAll
                    ])
         ) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
