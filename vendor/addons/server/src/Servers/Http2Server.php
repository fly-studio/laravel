<?php

namespace Addons\Server\Servers;

use RuntimeException;
use Addons\Server\Servers\HttpServer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Servers\Observer\HttpObserver;

class Http2Server extends HttpServer {

	protected function initServer()
	{
		parent::initServer();
		$this->server->set([
			'open_http2_protocol' => true,
		]);
	}
}
