<?php

namespace Addons\Func\Contracts;

use BadMethodCallException;

abstract class AbstractFactory {

	private $instances = [];

	public function make($method, ...$parameters)
	{
		$result = $this->$method(...$parameters);
		return $result;
	}

	public function makeSingleton($method, ...$parameters)
	{
		if (empty($this->instances[$method]))
			$this->instances[$method] = $this->$method(...$parameters);

		return $this->instances[$method];
	}

}
