<?php

namespace Addons\Server\Routing\Router;

use Closure;
use Addons\Server\Routing\Route;

trait NewTrait {

	/**
	 * Create a new route instance.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  mixed  $action
	 * @return \Addons\Server\Routing\Route
	 */
	protected function createRoute(int $type, $pattern, $action)
	{
		// If the route is routing to a controller we will parse the route action into
		// an acceptable array format before registering it and creating this route
		// instance itself. We need to build the Closure that will call this out.
		if ($this->actionReferencesController($action)) {
			$action = $this->convertToControllerAction($action);
		}

		$route = $this->newRoute(
			$type,
			$pattern,
			$action
		);

		// If we have groups that need to be merged, we will merge them now after this
		// route has already been created and is ready to go. After we're done with
		// the merge we will be ready to return the route back out to the caller.
		if ($this->hasGroupStack()) {
			$this->mergeGroupAttributesIntoRoute($route);
		}

		$this->addWhereClausesToRoute($route);

		return $route;
	}

	/**
	 * Create a new Route object.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  mixed  $action
	 * @return \Addons\Server\Routing\Route
	 */
	protected function newRoute(int $type, $pattern, $action)
	{
		return (new Route($type, $pattern, $action))
					->setRouter($this)
					->setContainer($this->container);
	}

	/**
	 * Determine if the action is routing to a controller.
	 *
	 * @param  array  $action
	 * @return bool
	 */
	protected function actionReferencesController($action)
	{
		if (! $action instanceof Closure) {
			return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
		}

		return false;
	}

	/**
	 * Merge the group stack with the controller action.
	 *
	 * @param  \Addons\Server\Routing\Route  $route
	 * @return void
	 */
	protected function mergeGroupAttributesIntoRoute($route)
	{
		$route->setAction($this->mergeWithLastGroup($route->getAction()));
	}

	/**
	 * Add a controller based route action to the action array.
	 *
	 * @param  array|string  $action
	 * @return array
	 */
	protected function convertToControllerAction($action)
	{
		if (is_string($action)) {
			$action = ['uses' => $action];
		}

		// Here we'll merge any group "uses" statement if necessary so that the action
		// has the proper clause for this property. Then we can simply set the name
		// of the controller on the action and return the action array for usage.
		if (! empty($this->groupStack)) {
			$action['uses'] = $this->prependGroupNamespace($action['uses']);
		}

		// Here we will set this controller name on the action array just so we always
		// have a copy of it for reference if we need it. This can be used while we
		// search for a controller name or do some other type of fetch operation.
		$action['controller'] = $action['uses'];

		return $action;
	}

	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 *
	 * @param  \Addons\Server\Routing\Route  $route
	 * @return \Addons\Server\Routing\Route
	 */
	protected function addWhereClausesToRoute($route)
	{
		$route->where(array_merge(
			$this->patterns, $route->getAction()['where'] ?? []
		));

		return $route;
	}
}
