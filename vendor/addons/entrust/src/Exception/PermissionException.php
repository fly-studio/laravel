<?php

namespace Addons\Entrust\Exception;

use RuntimeException;

class PermissionException extends RuntimeException
{
	public function __construct()
	{
		$this->message = 'You have no permission to access this page.';
	}
}
