<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Listeners\HttpListener;

class WebSocketListener extends HttpListener {

	public function onOpen(\swoole_http_request $request)
	{

	}

	public function onMessage(\swoole_websocket_frame $frame)
	{
		$binder = $this->pool->get($frame->fd);
		if (empty($binder))
			return;

		$this->updateServerOptions($binder->options(), $frame->fd);

		$this->recv($binder, $frame->data, $frame->opcode);
	}

	public function onHandShake(\swoole_http_request $request, \swoole_http_response $response, bool $result)
	{

	}
}
