<?php

namespace Addons\Server\Contracts;

use Addons\Server\Structs\ConnectBinder;
use Addons\Func\Contracts\TraitsBootTrait;
use Addons\Server\Contracts\AbstractServer;

abstract class AbstractListener {

	use TraitsBootTrait;

	protected $server;

	public function __construct(AbstractServer $server)
	{
		$this->server = $server;

		$this->bootIfNotBooted();
	}

	protected function recv(ConnectBinder $binder, ...$args)
	{
		$this->server->handle($binder, ...$args);
	}

}
