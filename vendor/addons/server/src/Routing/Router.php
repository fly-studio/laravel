<?php

namespace Addons\Server\Routing;

use Addons\Server\Routing\Route;
use Illuminate\Container\Container;
use Addons\Server\Routing\Router\NewTrait;
use Addons\Server\Routing\Router\RunTrait;
use Addons\Server\Routing\Router\BindTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Func\Contracts\AbstractGroupLoader;
use Addons\Server\Routing\Router\MiddlewareTrait;

class Router extends AbstractGroupLoader {

	use BindTrait, MiddlewareTrait, NewTrait, RunTrait;

	protected $routes = [];

	/**
	 * The globally available parameter patterns.
	 *
	 * @var array
	 */
	protected $patterns = [];

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
		return $this->matchAgainstRoutes($this->routes, $request);
	}

	protected function matchAgainstRoutes(array $routes, AbstractRequest $request)
	{
		// sort by type
		$routes = collect($routes)->sortBy(function ($route) {
			return $route->getType();
		});

		//find the first route
		return $routes->first(function($route) use($request){
			return $route->matches($request);
		});
	}

	/**
	 * raw内容，区分大小写
	 * $pattern == $request->keywords()
	 *
	 * @param  string $pattern [description]
	 * @param  [type] $action  [description]
	 * @return [type]          [description]
	 */
	public function raw(string $pattern, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_RAW, $pattern, $action);
		return $this;
	}

	/**
	 * 类似web路由中的参数 player/{id}，/ 符号不是特殊符号(在web路由中是)
	 * 路由内部函数($pattern, $request->keywords());
	 *
	 * @param  string $pattern
	 * @param  [type] $action  [description]
	 * @return [type]          [description]
	 */
	public function param(string $pattern, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_PARAM, $pattern, $action);
		return $this;
	}

	/**
	 * 正则匹配，完整的正则表达式
	 * preg_match($pattern, $request->keywords());
	 *
	 * @param  string $pattern [description]
	 * @param  [type] $action  [description]
	 * @return [type]          [description]
	 */
	public function regex(string $pattern, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_REGEX, $pattern, $action);
		return $this;
	}

	/**
	 * 一个回调确认是否匹配
	 * $callable($request->keywords())
	 * 需要返回true/false
	 *
	 * @param  callable $callable [description]
	 * @param  [type]   $action   [description]
	 * @return [type]             [description]
	 */
	public function callable(callable $callable, $action)
	{
		$this->routes[] = $this->createRoute(Route::TYPE_CALL, $pattern, $action);
		return $this;
	}

	/**
	 * Get the global "where" patterns.
	 *
	 * @return array
	 */
	public function getPatterns()
	{
		return $this->patterns;
	}

	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Set a global where pattern on all routes.
	 *
	 * @param  string  $key
	 * @param  string  $pattern
	 * @return void
	 */
	public function pattern($key, $pattern)
	{
		$this->patterns[$key] = $pattern;
	}

	/**
	 * Set a group of global where patterns on all routes.
	 *
	 * @param  array  $patterns
	 * @return void
	 */
	public function patterns($patterns)
	{
		foreach ($patterns as $key => $pattern) {
			$this->pattern($key, $pattern);
		}
	}

}
