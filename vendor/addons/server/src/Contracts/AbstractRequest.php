<?php

namespace Addons\Server\Contracts;

use Closure;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ConnectBinder;

abstract class AbstractRequest {

	protected $binder;
	protected $routeResolver;
	protected $userResolver;

	public static function build(...$args)
	{
		return new static(...$args);
	}

	abstract public function keywords(): string;

	public function options(): ServerOptions
	{
		return $this->binder->options();
	}

	public function binder(): ConnectBinder
	{
		return $this->binder;
	}

	public function with(ConnectBinder $binder)
	{
		$this->binder = $binder;
		return $this;
	}

	/**
	 * Get the route resolver callback.
	 *
	 * @return \Closure
	 */
	public function getRouteResolver()
	{
		return $this->routeResolver ?: function () {
			//
		};
	}

	public function setRouteResolver(Closure $callback)
	{
		$this->routeResolver = $callback;
		return $this;
	}

	/**
     * Get the user making the request.
     *
     * @param  string|null  $guard
     * @return mixed
     */
    public function user($guard = null)
    {
        return call_user_func($this->getUserResolver(), $guard);
    }

	/**
	 * Get the user resolver callback.
	 *
	 * @return \Closure
	 */
	public function getUserResolver()
	{
		return $this->userResolver ?: function () {
			//
		};
	}

	/**
	 * Set the user resolver callback.
	 *
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function setUserResolver(Closure $callback)
	{
		$this->userResolver = $callback;

		return $this;
	}

}
