<?php

namespace Addons\Server\Protocols\Grpc;

use Addons\Server\Protocols\Grpc\Request;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Http\Fire as HttpFire;
use Addons\Server\Contracts\AbstractRequest;

class Fire extends HttpFire {

	public function analyzing(ServerOptions $options, ?string $raw, \swoole_http_request $nativeRequest = null) : AbstractRequest
	{
		return new Request($options, $nativeRequest);
	}

}
