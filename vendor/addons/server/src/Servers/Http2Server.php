<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\HttpServer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Servers\Observers\HttpObserver;

class Http2Server extends HttpServer {

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		parent::initServer($server, $config);

		$server->set([
			'open_http2_protocol' => true,
		]);
	}
}
