<?php

namespace Addons\Sensor;

use Addons\Sensor\Sensor;
use Addons\Sensor\Ruling\Ruler;

class Factory {

	public $ruler;

	public function __construct(Ruler $rule)
	{
		$this->ruler = $ruler;
	}

	public function make($key, $attributes, $replace = [])
	{
		return new Sensor($this->ruler, $key, $attributes, $replace);
	}

}