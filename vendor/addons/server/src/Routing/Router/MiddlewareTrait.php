<?php

namespace Addons\Server\Routing\Router;

use Addons\Server\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Routing\SortedMiddleware;
use Illuminate\Routing\MiddlewareNameResolver;

trait MiddlewareTrait {

	/**
	 * All of the short-hand keys for middlewares.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * All of the middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [];

	/**
	 * The priority-sorted list of middleware.
	 *
	 * Forces the listed middleware to always be in the given order.
	 *
	 * @var array
	 */
	public $middlewarePriority = [];

	/**
	 * Gather the middleware for the given route with resolved class names.
	 *
	 * @param  \Addons\Server\Routing\Route  $route
	 * @return array
	 */
	public function gatherRouteMiddleware(Route $route)
	{
		$middleware = collect($route->gatherMiddleware())->map(function ($name) {
			return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
		})->flatten();

		return $this->sortMiddleware($middleware);
	}

	/**
	 * Get all of the defined middleware short-hand names.
	 *
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Register a short-hand name for a middleware.
	 *
	 * @param  string  $name
	 * @param  string  $class
	 * @return $this
	 */
	public function aliasMiddleware($name, $class)
	{
		$this->middleware[$name] = $class;

		return $this;
	}

	/**
	 * Check if a middlewareGroup with the given name exists.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function hasMiddlewareGroup($name)
	{
		return array_key_exists($name, $this->middlewareGroups);
	}

	/**
	 * Get all of the defined middleware groups.
	 *
	 * @return array
	 */
	public function getMiddlewareGroups()
	{
		return $this->middlewareGroups;
	}

	/**
	 * Register a group of middleware.
	 *
	 * @param  string  $name
	 * @param  array  $middleware
	 * @return $this
	 */
	public function middlewareGroup($name, array $middleware)
	{
		$this->middlewareGroups[$name] = $middleware;

		return $this;
	}

	/**
	 * Add a middleware to the beginning of a middleware group.
	 *
	 * If the middleware is already in the group, it will not be added again.
	 *
	 * @param  string  $group
	 * @param  string  $middleware
	 * @return $this
	 */
	public function prependMiddlewareToGroup($group, $middleware)
	{
		if (isset($this->middlewareGroups[$group]) && ! in_array($middleware, $this->middlewareGroups[$group])) {
			array_unshift($this->middlewareGroups[$group], $middleware);
		}

		return $this;
	}

	/**
	 * Sort the given middleware by priority.
	 *
	 * @param  \Illuminate\Support\Collection  $middlewares
	 * @return array
	 */
	protected function sortMiddleware(Collection $middlewares)
	{
		return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
	}

	/**
	 * Add a middleware to the end of a middleware group.
	 *
	 * If the middleware is already in the group, it will not be added again.
	 *
	 * @param  string  $group
	 * @param  string  $middleware
	 * @return $this
	 */
	public function pushMiddlewareToGroup($group, $middleware)
	{
		if (! array_key_exists($group, $this->middlewareGroups)) {
			$this->middlewareGroups[$group] = [];
		}

		if (! in_array($middleware, $this->middlewareGroups[$group])) {
			$this->middlewareGroups[$group][] = $middleware;
		}

		return $this;
	}
}
