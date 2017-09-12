<?php

namespace Addons\Func\Contracts;

use BadMethodCallException;

trait MutatorTrait {

	public function method_exists($method_name)
	{
		return method_exists($this, $method_name) ? true : property_exists($this, $method_name);
	}

	public function __call($method, array $parameters)
	{
		if (stripos($method, 'set') === 0 || stripos($method, 'get') === 0)
			$method = lcfirst(substr($method, 3));

		if ($this->method_exists($method))
		{
			if (empty($parameters))
				return $this->$method;
			else
			{
				list($this->$method, ) = $parameters;
				return $this;
			}
		}

		throw new BadMethodCallException("Method [{$method}] does not exist.");
	}
}
