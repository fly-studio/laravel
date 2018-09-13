<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Protocols\Http\Request;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractProtocol;

class Protocol extends AbstractProtocol {

	public function decode(ConnectBinder $binder, ...$args) : ?AbstractRequest
	{
		$request = $args[0];
		$response = $args[1];
		//To Do
		return new Request(...$args);
	}

	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		//To Do
		//
		return $response;
	}

}
