<?php

namespace Addons\Server\Contracts;

use BadMethodCallException;
use Addons\Server\Structs\ServerOptions;

abstract class AbstractFactory {

	protected $options = null;
	private $instances = [];

	public function options(ServerOptions $options = null)
	{
		if (is_null($options)) return $this->options;

		$this->options = $options;
		return $this;
	}

	public function make($method, ...$parameters)
	{
		$result = $this->$method();
		$result->boot(...$parameters);
		return $result;
	}

	public function makeSingleton($method, ...$parameters)
	{
		if (empty($this->instances[$method]))
		{
			$this->instances[$method] = $this->$method();
			$this->instances[$method]->boot(...$parameters);
		}

		return $this->instances[$method];
	}

}
