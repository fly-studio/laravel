<?php

namespace Addons\Server\Servers;

use RuntimeException;
use Addons\Server\Servers\HttpServer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Servers\Observer\WebSocketObserver;

class WebSocketServer extends HttpServer {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect', 'Message', 'Open']; // no Recive/Pack add Request

	protected function createServer()
	{
		$this->server = new \swoole_websocket_server($this->config->host()->host(), $this->config->host()->port(), $this->config->daemon() ? SWOOLE_PROCESS : SWOOLE_BASE, SWOOLE_SOCK_TCP | (!empty($this->config->ssl_cert_file()) && !empty($this->config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function observe()
	{
		$this->observer = new WebSocketObserver($this);

		foreach($this->observerListeners as $method)
			$this->server->on($method, [$this->observer, 'on'.$method]);
	}

}
