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

	/**
	 *
	 * 注意：如果覆盖同名监听函数，那只会触发这次设置的监听函数，之前设置的同名监听函数会被删除
	 * 这里的覆盖并不是指类的覆盖，而是指覆盖类中的回调函数名(onXXX之类)
	 *
	 * @param string|AbstractListener   $classOrName 类名或者类实例
	 * @param bool|boolean $overwrite
	 */
	public function addClassListener($classOrName, bool $overwrite = false)
	{
		if (!is_subclass_of($classOrName, AbstractListener::class))
			throw new RuntimeException("\$classOrName must be a subclass of AbstractListener.");

		$class = is_string($classOrName) ? new $classOrName($this->server) : $classOrName;

		$ref = new ReflectionClass($class);
		foreach($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$name = $method->getShortName();
			if (strpos($name, 'on') !== 0 || !method_exists($this, $name))
				continue;

			$this->addListener($name, [$class, $name], $overwrite);
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
