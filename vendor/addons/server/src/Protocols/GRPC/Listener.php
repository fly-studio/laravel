<?php

namespace Addons\Server\Protocols\GRPC;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\GRPC\Request;
use Addons\Server\Protocols\GRPC\Response;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\Listeners\AbstractHttpListener;

class Listener extends AbstractHttpListener {

	public function doRequest(ServerOptions $options, ?string $raw): AbstractRequest
	{
		$server = $options->server();
	}

}
