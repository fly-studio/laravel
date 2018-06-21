<?php

namespace Addons\Server\Protocols\Grpc;

use Addons\Server\Protocols\Grpc\Request;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Protocols\Http\Protocol as HttpProtocol;

class Protocol extends HttpProtocol {

	public function decode(ServerOptions $options, ...$args) : ?AbstractRequest
	{
		// To Do
		return new Request(...$args);
	}

}
