<?php

namespace Addons\Server\Protocols\WebSocket;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Protocols\WebSocket\Request;

class Fire extends AbstractFire {

	public function analyzing(ServerOptions $options, ?string $raw, int $opcode = null) : AbstractRequest
	{
		return new Request($options, $raw, $opcode);
	}

}
