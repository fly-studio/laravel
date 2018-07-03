<?php

namespace Addons\Server\Routing\Route;

use ReflectionFunction;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Routing\RouteParameterBinder;
use Addons\Server\Routing\Route\DependencyResolverTrait;

trait RunTrait {

	use DependencyResolverTrait;

	/**
	 * Run the route action and return the response.
	 *
	 * @return mixed
	 */
	public function run(AbstractRequest $request)
	{
		$this->compileRoute();

		$parameters = $this->parametersWithoutNulls(
				(new RouteParameterBinder($this))->parameters($request)
		);

		$parameters = array_merge(['request' => $request], $parameters);

		if ($this->isControllerAction()) {
			return $this->runController($parameters);
		}

		return $this->runCallable($parameters);
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @return mixed
	 */
	protected function runCallable(array $parameters)
	{
		$callable = $this->action['uses'];

		return $callable(...array_values($this->resolveMethodDependencies(
			$parameters,
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
	protected function runController(array $parameters)
	{
		return $this->controllerDispatcher()->dispatch(
			$parameters,
			$this->getController(),
			$this->getControllerMethod()
		);
	}
}
