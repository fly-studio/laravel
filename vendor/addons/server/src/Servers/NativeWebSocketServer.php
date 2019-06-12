<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\NativeTrait;
use Addons\Server\Contracts\AbstractProtocol;

class NativeWebSocketServer extends WebSocketServer {

	use NativeTrait;

	public function loadWebRoutes(string $file_path, string $namespace = 'App\\Tcp\\Controllers')
	{
		throw new \RunTimeException('Native WebSocket server does not need to define Http routes.');
	}

}
