<?php

namespace Addons\Server\Listeners\Internal;

use Addons\Server\Structs\ServerOptions;

trait OptionsTrait {

	protected $pool;

	protected function bootOptionsTrait()
	{
		$this->pool = $this->server->pool();
	}

	protected function makeServerOptions($fd)
	{
		//TCP only
		$client_info = $this->server->getClientInfo($fd);

		$options = new ServerOptions($this->server);
		$options
			->unique($fd)
			->file_descriptor($fd)
			->socket_type($client_info['socket_type'])
			->reactor_id($client_info['reactor_id'])
			->server_port($client_info['server_port'])
			->client_ip($client_info['remote_ip'])
			->client_port($client_info['remote_port'])
			->connect_time($client_info['connect_time'])
			->last_time($client_info['last_time'])
			->close_errno($client_info['close_errno'])
			;

		if (isset($client_info['websocket_status']))
			$options->websocket_status($client_info['websocket_status']);

		return $options;
	}

	protected function updateServerOptions(ServerOptions $options)
	{
		// TCP only
		$client_info = $this->server->getClientInfo($options->file_descriptor());
		$options
			->last_time($client_info['last_time'])
			->close_errno($client_info['close_errno'])
			;

		if (isset($client_info['websocket_status']))
			$options->websocket_status($client_info['websocket_status']);
	}

}
