<?php

namespace Addons\Server\Routing\Router;

use Addons\Server\Routing\Route;
use Illuminate\Routing\RouteBinding;

trait BindTrait {

	/**
	 * The registered route value binders.
	 *
	 * @var array
	 */
	protected $binders = [];

	/**
	 * Call the binding callback for the given key.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @param  Route  $route
	 * @return mixed
	 */
	protected function performBinding($key, $value, Route $route)
	{
		return call_user_func($this->binders[$key], $value, $route);
	}

	/**
	 * Add a new route parameter binder.
	 *
	 * @param  string  $key
	 * @param  string|callable  $binder
	 * @return void
	 */
	public function bind($key, $binder)
	{
		$this->binders[str_replace('-', '_', $key)] = RouteBinding::forCallback(
			$this->container, $binder
		);
	}

	/**
	 * Register a model binder for a wildcard.
	 *
	 * @param  string  $key
	 * @param  string  $class
	 * @param  \Closure|null  $callback
	 * @return void
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function model($key, $class, Closure $callback = null)
	{
		$this->bind($key, RouteBinding::forModel($this->container, $class, $callback));
	}

	/**
	 * Get the binding callback for a given binding.
	 *
	 * @param  string  $key
	 * @return \Closure|null
	 */
	public function getBindingCallback($key)
	{
		if (isset($this->binders[$key = str_replace('-', '_', $key)])) {
			return $this->binders[$key];
		}
	}

	/**
	 * Substitute the route bindings onto the route.
	 *
	 * @param  Route  $route
	 * @return Routep
	 */
	public function substituteBindings(Route $route)
	{
		foreach ($route->parameters() as $key => $value) {
			if (isset($this->binders[$key])) {
				$route->setParameter($key, $this->performBinding($key, $value, $route));
			}
		}

		return $route;
	}
}
