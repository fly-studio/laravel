<?php

namespace Addons\Core;

use BadMethodCallException;

trait MutatorTrait {

	public function method_exists($method_name)
	{
		return method_exists($this, $method_name) ? true : property_exists($this, $method_name);
	}

	public function __call($method, $parameters)
	{
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