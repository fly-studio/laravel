<?php

namespace Addons\Server;

use Closure;
use RuntimException;
use Addons\Server\Servers\Server;

class Kernel {

	protected $server;

	public function __construct()
	{
		//未来将设置中间件
	}

	public function run()
	{
		if (empty($this->server))
			throw new RuntimException('Need handled a Server instance.');

		$this->server->run();
	}

	public function handle(Server $server)
	{
		$this->server = $server;
	}
}
