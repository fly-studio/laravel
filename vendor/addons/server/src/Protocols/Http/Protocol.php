<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Protocols\Http\Request;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Protocols\Http\Responses\Response;

class Protocol extends AbstractProtocol {

	public function decode(ConnectBinder $binder, ...$args) : ?AbstractRequest
	{
		return new Request(...$args);
	}

	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		if ($response instanceof AbstractResponse) {
			return $response;
		} else {
			return new Response(@strval($response));
		}

	}

}
