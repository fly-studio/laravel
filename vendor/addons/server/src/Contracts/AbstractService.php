<?php

namespace Addons\Server\Contracts;

use Addons\Server\Structs\ServerOptions;

abstract class AbstractService {

	public static function build(...$args)
	{
		return new static(...$args);
	}
}
