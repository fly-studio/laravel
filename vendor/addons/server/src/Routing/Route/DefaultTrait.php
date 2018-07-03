<?php

namespace Addons\Server\Routing\Route;

trait DefaultTrait {

	/**
	 * The default values for the route.
	 *
	 * @var array
	 */
	public $defaults = [];

	/**
	 * Set a default value for the route.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return $this
	 */
	public function defaults($key, $value)
	{
		$this->defaults[$key] = $value;

		return $this;
	}

	public function getDefaults()
	{
		return $this->defaults;
	}
}
