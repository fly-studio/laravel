<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\Server;
use Addons\Server\Servers\Observer\HttpObserver;

class HttpServer extends Server {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect']; // no Recive/Pack add Request

	protected function createServer()
	{
		$this->server = new \swoole_http_server($this->config->host()->host(), $this->config->host()->port(), $this->config->daemon() ? SWOOLE_PROCESS : SWOOLE_BASE, SWOOLE_SOCK_TCP | (!empty($this->config->ssl_cert_file()) && !empty($this->config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function initServer()
	{
		//extra http config
		$config = $this->config;
		if (!empty($this->config->ssl_cert_file()) && !empty($config->ssl_key_file()))
		{
			$this->server->set([
				'ssl_cert_file' => $config->ssl_cert_file(),
				'ssl_key_file' => $config->ssl_key_file(),
				'ssl_ciphers' => $config->ssl_ciphers(),
				'ssl_method' => $config->ssl_method(),
			]);
		}
		$this->server->set([
			'upload_tmp_dir' => $config->upload_tmp_dir(),
			'http_parse_post' => $config->http_parse_post(),
			'package_max_length' => $config->package_max_length(),
			'document_root' => $config->document_root(),
			'enable_static_handler' => $config->enable_static_handler(),
		]);

		parent::initServer();
	}

	protected function observe()
	{
		$this->observer = new HttpObserver($this);

		foreach($this->observerListeners as $method)
			$this->server->on($method, [$this->observer, 'on'.$method]);
	}
}
