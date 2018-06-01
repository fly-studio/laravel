<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\Server;
use Addons\Server\Servers\Observer\HttpObserver;

class HttpServer extends Server {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop']; // no Connect/Recive/Pack add Request

	protected function createServer()
	{
		$this->server = new \swoole_http_server($this->config->listen()->host(), $this->config->listen()->port());
	}

	protected function initServer()
	{
		parent::initServer();

		//extra http config
		$config = $this->config;
		$this->server->set([
			'upload_tmp_dir' => $config->upload_tmp_dir(),
			'http_parse_post' => $config->http_parse_post(),
			'package_max_length' => $config->package_max_length(),
			'document_root' => $config->document_root(),
			'enable_static_handler' => $config->enable_static_handler(),
		]);
	}

	protected function observe()
	{
		$this->observer = new HttpObserver($this);

		foreach($this->observerListeners as $method)
			$this->server->on($method, [$this->observer, 'on'.$method]);
	}
}
