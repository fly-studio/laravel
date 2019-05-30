<?php

namespace Addons\Server\Servers;

class NativeHttp2Server extends NativeHttpServer {

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		parent::initServer($server, $config);

		$server->set([
			'open_http2_protocol' => true,
		]);
	}

}
