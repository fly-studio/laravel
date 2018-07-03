<?php

namespace Addons\Server\Routing;

use Addons\Server\Routing\Router;
use Illuminate\Container\Container;
use Addons\Server\Routing\Route\RunTrait;
use Addons\Server\Routing\Route\WhereTrait;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Routing\Route\ActionTrait;
use Addons\Server\Routing\Route\CompileTrait;
use Addons\Server\Routing\Route\DefaultTrait;
use Addons\Server\Routing\Route\ParameterTrait;
use Addons\Server\Routing\Route\ValidatorTrait;
use Addons\Server\Routing\Route\ControllerTrait;
use Addons\Server\Routing\Route\MiddlewareTrait;

class Route {

	use ActionTrait, CompileTrait, ControllerTrait, DefaultTrait, MiddlewareTrait, ParameterTrait, RunTrait, ValidatorTrait, WhereTrait;

	const TYPE_RAW = 1;
	const TYPE_PARAM = 2;
	const TYPE_REGEX = 3;
	const TYPE_CALL = 4;

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

	protected $pattern;

	public function __construct(int $type, $pattern, $action)
	{
		$this->type = $type;
		$this->pattern = $pattern;
		$this->action = $this->parseAction($action);
	}

	/**
	 * 当前路由是否匹配
	 *
	 * @param  [AbstractRequest] $request
	 * @return [bool]
	 */
	public function matches(AbstractRequest $request): bool
	{
		$this->compileRoute();

		foreach (static::getValidators() as $validator) {
			if (! $validator->matches($this, $request)) {
				return false;
			}
		}

		return true;
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

	public function getPattern()
	{
		return $this->pattern;
	}

	public function getType()
	{
		return $this->type;
	}

}
