<?php

namespace Addons\Server\Protocols\Grpc;

use Addons\Server\Protocols\Grpc\Request;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Protocols\Http\Protocol as HttpProtocol;

class Protocol extends HttpProtocol {

	public function decode(ConnectBinder $binder, ...$args) : ?AbstractRequest
	{
		// To Do
		return new Request(...$args);
	}

}
