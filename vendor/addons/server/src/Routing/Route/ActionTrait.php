<?php

namespace Addons\Server\Routing\Route;

use Illuminate\Support\Arr;
use Illuminate\Routing\RouteAction;

trait ActionTrait {

	/**
	 * The route action array.
	 *
	 * @var array
	 */
	protected $action = [];

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

	public function actionExists($key)
	{
		return isset($this->action[$key]);
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
}
