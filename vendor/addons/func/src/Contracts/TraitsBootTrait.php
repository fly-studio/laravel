<?php

namespace Addons\Func\Contracts;

trait TraitsBootTrait {

	/**
	 * If booted.
	 *
	 * @var array
	 */
	protected $booted = false;

	/**
	 * Check if the class needs to be booted and if so, do it.
	 *
	 * @return void
	 */
	public function bootIfNotBooted(...$args)
	{
		if (!$this->booted) {
			$this->booted = true;

			$this->boot(...$args);
		}
	}

	/**
	 * The "booting" method of the class.
	 *
	 * @return void
	 */
	protected function boot(...$args)
	{
		foreach (class_uses_recursive(static::class) as $trait) {
			if (method_exists($this, $method = 'boot'.class_basename($trait))) {
				call_user_func_array([$this, $method], $args);
			}
		}
	}
}
