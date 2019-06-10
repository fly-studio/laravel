<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\NativeTrait;
use Addons\Server\Contracts\AbstractProtocol;

class NativeWebSocketServer extends WebSocketServer {

	use NativeTrait {
		handle as webHandle;
	}

	public function loadWebRoutes(string $file_path, string $namespace = 'App\\Tcp\\Controllers')
	{
		throw new \RunTimeException('Native WebSocket server does not need to define Http routes.');
	}

	/**
	 * 设置Ws/Http解码协议
	 *
	 * @param  AbstractProtocol $capture 解码协议实例
	 * @return $this
	 */
	public function capture(AbstractProtocol $capture, AbstractProtocol $webCapture = null)
	{
		if (!is_null($webCapture))
		{
			$this->webCapture = $webCapture;
			$webCapture->bootIfNotBooted($this);
		}

		return parent::capture($capture);
	}

}
