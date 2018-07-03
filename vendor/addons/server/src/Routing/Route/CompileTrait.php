<?php

namespace Addons\Server\Routing\Route;

use Addons\Server\Routing\RouteCompiler;
use Addons\Server\Routing\CompiledRoute;

trait CompileTrait {

	/**
	 * The compiled version of the route.
	 *
	 * @var \Addons\Server\Routing\CompiledRoute
	 */
	public $compiled;

	protected function compileRoute()
	{
		if (! $this->compiled)
		{
			switch ($this->getType()) {
				case static::TYPE_PARAM:
					$this->compiled = (new RouteCompiler($this))->compile();
					break;
				case static::TYPE_REGEX:
					$this->compiled = new CompiledRoute(null, $this->getPattern(), []);
				default:
					break;
			}
		}

		return $this->compiled;
	}
}
