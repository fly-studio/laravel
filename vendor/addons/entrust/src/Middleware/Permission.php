<?php
namespace Addons\Entrust\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Addons\Core\Http\OutputResponse;

class Permission
{
	protected $auth;

	/**
	 * Creates a new instance of the middleware.
	 *
	 * @param Guard $auth
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Closure $next
	 * @param  $permissions
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$permissions)
	{
		if ($this->auth->guest() || !$request->user()->can($permissions))
			return (new OutputResponse)->setRequest($request)->setResult('failure')->setMessage('auth.permission_forbidden')->setStatusCode(403);

		return $next($request);
	}
}
