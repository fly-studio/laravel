<?php

namespace Addons\Server\Protocols\GRPC;

use Addons\Server\Protocols\GRPC\Request;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Http\Fire as HttpFire;
use Addons\Server\Contracts\AbstractRequest;

class Fire extends HttpFire {

	public function analyzing(ServerOptions $options, ?string $raw, \swoole_http_request $nativeRequest = null) : AbstractRequest
	{
		return new Request($options, $nativeRequest);
	}

}
