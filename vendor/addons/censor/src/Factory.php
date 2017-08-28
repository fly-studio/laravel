<?php

namespace Addons\Censor;

use Addons\Censor\Censor;
use Addons\Censor\Ruling\Ruler;

class Factory {

	public $ruler;

	public function __construct(Ruler $ruler)
	{
		$this->ruler = $ruler;
	}

	public function make($key, $attributes, $replace = [])
	{
		return new Censor($this->ruler, $key, $attributes, $replace);
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace($namespace, $hint)
	{
		$this->ruler->addNamespace($namespace, $hint);
	} 

}