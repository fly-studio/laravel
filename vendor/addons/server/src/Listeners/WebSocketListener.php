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

		$options = $binder->options();

		$this->updateServerOptions($options, $frame->fd);

		$options->logger('info', 'Websocket receive: ');
		$options->logger('debug', print_r($options->toArray(), true));
		$options->logger('hex', $frame->data);

		$this->recv($binder, $frame->data, $frame->opcode);
	}

	public function onHandShake(\swoole_http_request $request, \swoole_http_response $response, bool $result)
	{

	}

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$binder = $this->pool->get($request->fd);
		if (empty($binder))
			return;

		$this->updateServerOptions($binder->options());

		$binder->options()->logger('debug', 'Http '.$request->server['request_method'].' '.$request->server['request_uri'].(!empty($request->server['query_string']) ? '?'.$request->server['query_string'] : ''));

		$this->server->webHandle($binder, $request, $response);
	}
}
