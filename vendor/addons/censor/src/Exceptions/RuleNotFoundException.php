<?php

namespace Addons\Censor\Exceptions;

use RuntimeException;
use Addons\Censor\Ruling\Ruler;

class RuleNotFoundException extends RuntimeException {

	public function __construct(string $message, Ruler $ruler = null, string $key = null)
	{
		if (!empty($ruler) && !empty($key)) $message .= ' In directory '.$ruler->getPath($key);
		parent::__construct($message);
	}

}
