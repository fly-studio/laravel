<?php

namespace Addons\Server\Routing;

use Closure;
use LogicException;
use ReflectionFunction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Addons\Server\Routing\Router;
use Illuminate\Container\Container;
use Illuminate\Routing\RouteAction;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Routing\RouteDependencyResolverTrait;
use Addons\Server\Routing\ControllerDispatcher as ControllerDispatcherContract;

class Route {

	use RouteDependencyResolverTrait;

	/**
	 * The route action array.
	 *
	 * @var array
	 */
	protected $action = [];
	/**
	 * The router instance used by the route.
	 *
	 * @var \Addons\Server\Routing\Router
	 */
	protected $router;
	/**
	 * The container instance used by the route.
	 *
	 * @var \Illuminate\Container\Container
	 */
	protected $container;
	/**
	 * The array of matched parameters.
	 *
	 * @var array
	 */
	public $parameters = [];
	/**
     * The controller instance.
     *
     * @var mixed
     */
    public $controller;

	/**
	 * The computed gathered middleware.
	 *
	 * @var array|null
	 */
	public $computedMiddleware;

	public function __construct($action)
	{
		$this->action = $this->parseAction($action);
	}

	/**
	 * Set the container instance on the route.
	 *
	 * @param  \Illuminate\Container\Container  $container
	 * @return $this
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Set the router instance on the route.
	 *
	 * @param  \Addons\Server\Routing\Router  $router
	 * @return $this
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}

	/**
	 * Get the action array or one of its properties for the route.
	 *
	 * @param  string|null  $key
	 * @return mixed
	 */
	public function getAction($key = null)
	{
		return Arr::get($this->action, $key);
	}

	/**
	 * Set the action array for the route.
	 *
	 * @param  array  $action
	 * @return $this
	 */
	public function setAction(array $action)
	{
		$this->action = $action;

		return $this;
	}

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

	/**
	 * Get the middleware for the route's controller.
	 *
	 * @return array
	 */
	public function controllerMiddleware()
	{
		if (! $this->isControllerAction()) {
			return [];
		}

		return $this->controllerDispatcher()->getMiddleware(
			$this->getController(), $this->getControllerMethod()
		);
	}

	/**
	 * Get the dispatcher for the route's controller.
	 *
	 * @return \Illuminate\Routing\Contracts\ControllerDispatcher
	 */
	public function controllerDispatcher()
	{
		if ($this->container->bound(ControllerDispatcherContract::class)) {
			return $this->container->make(ControllerDispatcherContract::class);
		}

		return new ControllerDispatcherContract($this->container);
	}

	/**
	 * Parse the route action into a standard array.
	 *
	 * @param  callable|array|null  $action
	 * @return array
	 *
	 * @throws \UnexpectedValueException
	 */
	protected function parseAction($action)
	{
		return RouteAction::parse(null, $action);
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @return mixed
	 */
	public function run(AbstractRequest $request)
	{
		if ($this->isControllerAction()) {
			return $this->runController($request);
		}

		return $this->runCallable($request);
	}

	/**
	 * Checks whether the route's action is a controller.
	 *
	 * @return bool
	 */
	protected function isControllerAction()
	{
		return is_string($this->action['uses']);
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @return mixed
	 */
	protected function runCallable(AbstractRequest $request)
	{
		$callable = $this->action['uses'];

		return $callable(...array_values($this->resolveMethodDependencies(
			$this->parametersWithoutNulls($request),
			new ReflectionFunction($this->action['uses'])
		)));
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function runController(AbstractRequest $request)
	{
		return $this->controllerDispatcher()->dispatch(
			$this->parametersWithoutNulls($request),
			$this->getController(),
			$this->getControllerMethod()
		);
	}

	/**
	 * Get the controller instance for the route.
	 *
	 * @return mixed
	 */
	public function getController()
	{
		if (! $this->controller) {
			$class = $this->parseControllerCallback()[0];

			$this->controller = app()->make(ltrim($class, '\\'));
		}

		return $this->controller;
	}

	/**
	 * Get the controller method used for the route.
	 *
	 * @return string
	 */
	protected function getControllerMethod()
	{
		return $this->parseControllerCallback()[1];
	}

	/**
	 * Parse the controller.
	 *
	 * @return array
	 */
	protected function parseControllerCallback()
	{
		return Str::parseCallback($this->action['uses']);
	}

	/**
	 * Get the key / value list of parameters for the route.
	 *
	 * @return array
	 *
	 * @throws \LogicException
	 */
	public function parameters()
	{
		if (isset($this->parameters)) {
			return $this->parameters;
		}

		throw new LogicException('Route is not bound.');
	}

	/**
	 * Get the key / value list of parameters without null values.
	 *
	 * @return array
	 */
	public function parametersWithoutNulls(...$args)
	{
		return array_filter(array_merge($this->parameters(), $args), function ($p) {
			return ! is_null($p);
		});
	}

}
