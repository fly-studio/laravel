<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Http\Request;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractProtocol;

class Protocol extends AbstractProtocol {

	public function decode(ServerOptions $options , ...$args) : ?AbstractRequest
	{
		$request = $args[0];
		$response = $args[1];
		//To Do
		return new Request();
	}

	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		//To Do
	}

}
