<?php

namespace Addons\Server\Contracts;

use Addons\Server\Structs\ServerOptions;
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

	protected function recv(ServerOptions $options, ...$args)
	{
		$this->server->handle($options, ...$args);
	}

}
