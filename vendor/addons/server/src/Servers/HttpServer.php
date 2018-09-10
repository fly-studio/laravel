<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\Server;
use Addons\Server\Senders\HttpSender;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Observers\HttpObserver;
use Addons\Server\Listeners\HttpListener;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Structs\Config\ServerConfig;

class HttpServer extends Server {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect'];

	protected function createServer(ServerConfig $config): \swoole_server
	{
		return new \swoole_http_server($config->host()->host(), $config->host()->port(), $config->daemon() ? SWOOLE_PROCESS : SWOOLE_BASE, SWOOLE_SOCK_TCP | (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		//extra http config
		if (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()))
		{
			$server->set([
				'ssl_cert_file' => $config->ssl_cert_file(),
				'ssl_key_file' => $config->ssl_key_file(),
				'ssl_ciphers' => $config->ssl_ciphers(),
				'ssl_method' => $config->ssl_method(),
			]);
		}

		$server->set([
			'upload_tmp_dir' => $config->upload_tmp_dir(),
			'http_parse_post' => $config->http_parse_post(),
			'package_max_length' => $config->package_max_length(),
			'document_root' => $config->document_root(),
			'enable_static_handler' => $config->enable_static_handler(),
		]);

		parent::initServer($server, $config);
	}

	protected function makeSender(ServerOptions $options, ...$args): AbstractSender
	{
		//One tunnel has multi-http-stream
		//return $this->pool->getBindIf($options->unique(), 'http-sender', function() use($options, $args) {
			return new HttpSender($options, ...$args);
		//});
	}

	protected function createObserver(): AbstractObserver
	{
		return new HttpObserver($this);
	}

	protected function getAutoListeners(): array
	{
		return [HttpListener::class];
	}
}
