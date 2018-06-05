<?php

namespace Addons\Server\Routing;

use ReflectionParameter;
use Illuminate\Support\Arr;
use Illuminate\Routing\RouteDependencyResolverTrait as BaseRouteDependencyResolverTrait;

trait RouteDependencyResolverTrait {
	use BaseRouteDependencyResolverTrait;

	/**
	 * Attempt to transform the given parameter into a class instance.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @param  array  $parameters
	 * @return mixed
	 */
	protected function transformDependency(ReflectionParameter $parameter, $parameters)
	{
		$class = $parameter->getClass();

		// If the parameter has a type-hinted class, we will check to see if it is already in
		// the list of parameters. If it is we will just skip it as it is probably a model
		// binding and we do not want to mess with those; otherwise, we resolve it here.
		if ($class) {
			if (!is_null($instance = $this->alreadyInParameters($class->name, $parameters))) {
				return $instance;
			} else {
				return $parameter->isDefaultValueAvailable()
				? $parameter->getDefaultValue()
				: $this->container->make($class->name);
			}
		}
	}

	/**
	 * Determine if an object of the given class is in a list of parameters.
	 *
	 * @param  string  $class
	 * @param  array  $parameters
	 * @return bool
	 */
	protected function alreadyInParameters($class, array $parameters)
	{
		return Arr::first($parameters, function ($value) use ($class) {
			return $value instanceof $class;
		});
	}

}
