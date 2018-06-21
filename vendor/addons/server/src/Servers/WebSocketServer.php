<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\HttpServer;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Senders\WebSocketSender;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Observers\WebSocketObserver;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Listeners\WebSocketListener;

class WebSocketServer extends HttpServer {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect', 'Message', 'Open', 'HandShake'];

	protected function createServer(ServerConfig $config): \swoole_server
	{
		return new \swoole_websocket_server($config->host()->host(), $config->host()->port(), $config->daemon() ? SWOOLE_PROCESS : SWOOLE_BASE, SWOOLE_SOCK_TCP | (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function makeSender(ServerOptions $options, ...$args): AbstractSender
	{
		return $this->pool->getBindIf($options->unique(), 'ws-sender', function() use($options, $args) {
			return new WebSocketSender($options, ...$args);
		});
	}

	protected function createObserver(): AbstractObserver
	{
		return new WebSocketObserver($this);
	}

	protected function getAutoListeners(): array
	{
		return [WebSocketListener::class];
	}

}
