<?php

namespace Addons\Server\Routing\Route;

trait MiddlewareTrait {

	/**
	 * The computed gathered middleware.
	 *
	 * @var array|null
	 */
	public $computedMiddleware;

	/**
	 * Get all middleware, including the ones from the controller.
	 *
	 * @return array
	 */
	public function gatherMiddleware()
	{
		if (! is_null($this->computedMiddleware)) {
			return $this->computedMiddleware;
		}

		$this->computedMiddleware = [];

		return $this->computedMiddleware = array_unique(array_merge(
			$this->middleware(), $this->controllerMiddleware()
		), SORT_REGULAR);
	}

	/**
	 * Get or set the middlewares attached to the route.
	 *
	 * @param  array|string|null $middleware
	 * @return $this|array
	 */
	public function middleware($middleware = null)
	{
		if (is_null($middleware)) {
			return (array) ($this->action['middleware'] ?? []);
		}

		if (is_string($middleware)) {
			$middleware = func_get_args();
		}

		$this->action['middleware'] = array_merge(
			(array) ($this->action['middleware'] ?? []), $middleware
		);

		return $this;
	}
}
