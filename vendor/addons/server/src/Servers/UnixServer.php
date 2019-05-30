<?php

namespace Addons\Server\Servers;

use RuntimeException;
use Addons\Server\Servers\Server;
use Addons\Server\Observers\UnixObserver;
use Addons\Server\Listeners\UnixListener;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Structs\Config\ServerConfig;

class UnixServer extends Server {

	protected function validateConfig(ServerConfig $config)
	{
		if (strpos($config->host()->host(), '/') === false)
			throw new RuntimeException('Server host must be a file path.');
	}

	protected function createObserver(): AbstractObserver
	{
		return new UnixObserver($this);
	}

	protected function getSystemListeners(): array
	{
		return [UnixListener::class];
	}
}
