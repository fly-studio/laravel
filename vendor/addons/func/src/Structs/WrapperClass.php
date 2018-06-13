<?php

namespace Addons\Func\Structs;

use BadMethodCallException;

class WrapperClass {

	private $class;

	public function __construct($class)
	{
		$this->class = $class;
	}

	public function __call($method, $args)
	{
		if (method_exists($this->class, $method))
			return $this->class->$method(...$args);

		throw new BadMethodCallException(sprintf(
			'Method %s::%s does not exist.', static::class, $method
		));
	}
}
