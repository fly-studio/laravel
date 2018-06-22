<?php

namespace Addons\Server\Routing;

use Addons\Server\Routing\Route;
use Illuminate\Container\Container;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ServiceCallable;
use Addons\Server\Routing\Router\NewTrait;
use Addons\Server\Routing\Router\RunTrait;
use Addons\Server\Routing\Router\BindTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Func\Contracts\AbstractGroupLoader;
use Addons\Server\Routing\Router\MiddlewareTrait;

class Router extends AbstractGroupLoader {

	use BindTrait, MiddlewareTrait, RunTrait, NewTrait;

	protected $routes = [];

	/**
	 * Create a new Router instance.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @param  \Illuminate\Container\Container  $container
	 * @return void
	 */
	public function __construct(Dispatcher $events, Container $container = null)
	{
		$this->events = $events;
		$this->container = $container ?: new Container;
		$this->setLoadResolver(function($file_path, $router) {
			require $file_path;
		});
	}

	public function findRoute(AbstractRequest $request) : ?Route
	{
		$route = $this->matchAgainstRoutes($this->routes, $request);
		if (! is_null($route))
			return $route->bind($request); // bind the request to route

		return null;
	}

	protected function matchAgainstRoutes(array $routes, $request)
	{
		// sort by type
		$routes = collect($routes)->sortBy(function ($route) {
			return $route->type();
		});

		//find the first route
		return $routes->first(function($route) use($request){
			return $route->match($request);
		});
	}

	public function raw(string $eigenvalue, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_RAW, $eigenvalue, $action);
		return $this;
	}

	public function match(string $eigenvalue, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_MATCH, $eigenvalue, $action);
		return $this;
	}

	public function regex(string $eigenvalue, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_REGEX, $eigenvalue, $action);
		return $this;
	}

	public function callable(callable $callable, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_CALL, $eigenvalue, $action);
		return $this;
	}

}
