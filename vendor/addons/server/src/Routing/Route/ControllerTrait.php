<?php

namespace Addons\Server\Routing\Route;

use Illuminate\Support\Str;
use Addons\Server\Routing\ControllerDispatcher as ControllerDispatcherContract;

trait ControllerTrait {

	/**
	 * The controller instance.
	 *
	 * @var mixed
	 */
	public $controller;

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
	 * Checks whether the route's action is a controller.
	 *
	 * @return bool
	 */
	protected function isControllerAction()
	{
		return is_string($this->action['uses']);
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
}
