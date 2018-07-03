<?php

namespace Addons\Server\Routing\Router;

use RuntimeException;
use Illuminate\Routing\Events;
use Addons\Server\Routing\Route;
use Addons\Server\Routing\Pipeline;
use Addons\Server\Contracts\AbstractRequest;

trait RunTrait {

	/**
	 * Dispatch the request to a route and return the response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	public function dispatchToRoute(AbstractRequest $request)
	{
		$route = $this->findRoute($request);

		if (empty($route))
			return null;

		return $this->runRoute($request, $route);
	}

	/**
	 * Return the response for the given route.
	 *
	 * @param  \Addons\Server\Contracts\AbstractRequest  $request
	 * @param  \Illuminate\Routing\Route  $route
	 * @return mixed
	 */
	protected function runRoute(AbstractRequest $request, Route $route)
	{
		$request->setRouteResolver(function () use ($route) {
			return $route;
		});

		$this->events->dispatch(new Events\RouteMatched($route, $request));

		return $this->runRouteWithinStack($route, $request);
	}

	/**
	 * Run the given route within a Stack "onion" instance.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Addons\Server\Contracts\AbstractRequest  $request
	 * @return mixed
	 */
	protected function runRouteWithinStack(Route $route, AbstractRequest $request)
	{
		$shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
								$this->container->make('middleware.disable') === true;

		$middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);

		return (new Pipeline($this->container))
						->send($request)
						->through($middleware)
						->then(function ($request) use ($route) {
							return $route->run($request);
						});
	}

	/**
	 * Register a route matched event listener.
	 *
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function matched($callback)
	{
		$this->events->listen(Events\RouteMatched::class, $callback);
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 *
	 * @param  \Addons\Server\Contracts\AbstractRequest  $request
	 * @param  \Addons\Server\Contracts\AbstractReponse  $response
	 * @return void
	 */
	protected function terminateMiddleware($request, $response)
	{
		$shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
								$this->container->make('middleware.disable') === true;

		$middlewares = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);

		foreach ($middlewares as $middleware) {
			if (! is_string($middleware)) {
				continue;
			}

			list($name) = $this->parseMiddleware($middleware);

			$instance = $this->container->make($name);

			if (method_exists($instance, 'terminate')) {
				$instance->terminate($request, $response);
			}
		}
	}

	/**
	 * Parse a middleware string to get the name and parameters.
	 *
	 * @param  string  $middleware
	 * @return array
	 */
	protected function parseMiddleware($middleware)
	{
		list($name, $parameters) = array_pad(explode(':', $middleware, 2), 2, []);

		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}

		return [$name, $parameters];
	}

}
