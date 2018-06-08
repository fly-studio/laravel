<?php

namespace Addons\Server\Contracts\Listeners;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\Requests\AbstractRequest;

abstract class AbstractHttpListener extends AbstractProtocolListener {

	protected $logPrefix = '[HTTP Server]';

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$options = $this->pool->get($request->fd);
		if (empty($options))
			return;

		$this->updateServerOptions($options, $request->fd);

		$this->fire($options, null, $request, $response);
	}

	protected function fire(ServerOptions $options, ?string $raw, \swoole_http_request $nativeRequest = null, \swoole_http_response $nativeResponse = null)
	{
		try {
			$request = $this->fire->analyzing($options, null, $nativeRequest);
			if (empty($request))
				return;

			$response = $this->fire->handle($request, $nativeResponse);
			if (empty($response))
				return;

			$response->send($nativeResponse);

			if (is_callable($response->nextAction()))
				call_user_func($response->nextAction(), $response);

		} catch (\Exception $e) {
			$this->fire->failed($options, $e);
		}
	}


}
