<?php

namespace Addons\Server\Contracts\Listeners;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\Requests\AbstractRequest;

abstract class AbstractWebSocketListener  extends AbstractHttpListener {
	protected $logPrefix = '[WebSocket Server]';

	public function onOpen(\swoole_http_request $request)
	{

	}

	public function onMessage(\swoole_websocket_frame $frame)
	{
		$options = $this->pool->get($frame->fd);
		if (empty($options))
			return;

		$this->updateServerOptions($options, $frame->fd);

		$this->fire($options, $frame->data, $frame->opcode);
	}

	public function onHandShake(\swoole_http_request $request, \swoole_http_response $response): bool
	{
		// websocket握手连接算法验证
		$secWebSocketKey = $request->header['sec-websocket-key'];
		$patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
		if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
			$response->end();
			return false;
		}
		//echo $request->header['sec-websocket-key'];
		$key = base64_encode(sha1(
			$request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
			true
		));

		$headers = [
			'Upgrade' => 'websocket',
			'Connection' => 'Upgrade',
			'Sec-WebSocket-Accept' => $key,
			'Sec-WebSocket-Version' => '13',
		];

		// WebSocket connection to 'ws://127.0.0.1:9502/'
		// failed: Error during WebSocket handshake:
		// Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
		if (isset($request->header['sec-websocket-protocol']))
			$headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];

		foreach ($headers as $key => $val) {
			$response->header($key, $val);
		}

		$response->status(101);
		$response->end();
		//echo "connected!" . PHP_EOL;
		return true;
	}

	protected function fire(ServerOptions $options, ?string $raw, int $opcode = null)
	{
		try {
			$request = $this->fire->analyzing($options, $raw, $opcode);
			if (empty($request))
				return;

			$response = $this->fire->handle($request);
			if (empty($response))
				return;

			$response->send();

			if (is_callable($response->nextAction()))
				call_user_func($response->nextAction(), $response);

		} catch (\Exception $e) {
			$this->fire->failed($options, $e);
		}
	}

}
