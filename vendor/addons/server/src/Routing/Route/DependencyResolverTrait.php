<?php

namespace Addons\Server\Routing\Route;

use ReflectionParameter;
use Illuminate\Support\Arr;
use ReflectionFunctionAbstract;
use Addons\Server\Contracts\AbstractRequest;
use Illuminate\Routing\RouteDependencyResolverTrait as RouteDependencyResolverTrait;

trait DependencyResolverTrait {

	use RouteDependencyResolverTrait {
		resolveMethodDependencies as protected _resolveMethodDependencies;
	}


	/**
	 * Resolve the given method's type-hinted dependencies.
	 *
	 * @param  array  $parameters
	 * @param  \ReflectionFunctionAbstract  $reflector
	 * @return array
	 */
	public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
	{
		$parameters = $this->replaceMethodRequest($parameters, $reflector);
		return $this->_resolveMethodDependencies($parameters, $reflector);
	}

	private function replaceMethodRequest(array $parameters, ReflectionFunctionAbstract $reflector) : array
	{
		if (!isset($parameters['request']) || !($parameters['request'] instanceof AbstractRequest))
			return $parameters;

		$request = $parameters['request'];
		unset($parameters['request']);

		foreach($reflector->getParameters() as $key => $parameter)
		{
			$class = $parameter->getClass();
			if ($class && $class->isSubclassOf(AbstractRequest::class)) {
				$this->spliceIntoParameters($parameters, $key, $request);
				break;
			}
		}

		return $parameters;
	}
}
