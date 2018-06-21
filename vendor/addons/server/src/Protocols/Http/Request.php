<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	public function eigenvalue(): string
	{
		return $this->server['request_uri'];
	}

}
