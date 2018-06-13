<?php

namespace Addons\Server\Servers\Observer;

use Addons\Server\Servers\Observer\HttpObserver;
use Addons\Server\Contracts\Listeners\AbstractWebsocketListener;

class WebSocketObserver extends HttpObserver {

	public function setProtocolListener(AbstractWebsocketListener $listener)
	{
		$this->listener = $listener;
	}

	public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
	{
		$this->listener->onOpen($request);
	}

	public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
	{
		$this->listener->onMessage($frame);
	}

	public function onHandShake(\swoole_http_request $request, \swoole_http_response $response)
	{
		$result = $this->listener->onHandShake($request, $response);
		if ($result)
			$this->onOpen($this->server->nativeServer(), $request);
		return $result;
	}
}
