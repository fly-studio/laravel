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

	public function make(string $key, array $attributes, array $replacement = null)
	{
		return new Censor($this->ruler, $key, $attributes, $replacement);
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace(string $namespace, string $hint = null)
	{
		$this->ruler->addNamespace($namespace, $hint);
	}

}
