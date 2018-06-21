<?php

namespace Addons\Server\Contracts;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Addons\Func\Contracts\TraitsBootTrait;
use Addons\Server\Contracts\AbstractServer;
use Addons\Func\Contracts\EventListenerTrait;
use Addons\Server\Contracts\AbstractListener;

abstract class AbstractObserver {

	use TraitsBootTrait;
	use EventListenerTrait;

	protected $server;

	public function __construct(AbstractServer $server)
	{
		$this->server = $server;

		$this->bootIfNotBooted();
	}

	public function addClassListener(string $className)
	{
		if (!is_subclass_of($className, AbstractListener::class))
			throw new RuntimeException("\$className: {$className} must be a subclass of AbstractListener.");

		$class = new $className($this->server);
		$ref = new ReflectionClass($class);
		foreach($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$name = $method->getShortName();
			if (strpos($name, 'on') !== 0 || !method_exists($this, $name))
				continue;

			$this->addListener($name, [$class, $name]);
		}
		return $this;
	}

	public function removeClassListener(string $className)
	{
		foreach($this->listeners as $name => $list)
			foreach($list as $key => $listener)
				if (is_array($listener) && get_class($listener[0]) === $className)
					unset($this->listeners[$name][$key]);

		return $this;
	}
}
