<?php

namespace Addons\Server\Protocols\Http;

use RuntimeException;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Http\Request;
use Addons\Server\Protocols\Http\Response;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

class Fire extends AbstractFire {

	public function analyzing(ServerOptions $options, ?string $raw, \swoole_http_request $nativeRequest = null) : AbstractRequest
	{
		if (empty($nativeRequest))
			throw new RuntimeException('$nativeRequest must be a swoole_http_request.');

		return new Request($options, $nativeRequest);
	}

	public function handle(AbstractRequest $request, \swoole_http_response $nativeResponse = null): ?AbstractResponse
	{
		if (empty($nativeResponse))
			throw new RuntimeException('$nativeResponse must be a swoole_http_response.');

		$response = $this->server()->router()->dispatchToRoute($request);

		if ($response instanceof Response)
			$response = $response;
		else
			$response = new Response(@strval($response));

		$response->options($request->options());
		$response->boot();

		$response->prepare($request);

		return $response;
	}

}
