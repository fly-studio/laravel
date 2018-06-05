<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Protocols\Raw\Request;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\AbstractRequest;

class Fire extends AbstractFire {

	public function analyzing(ServerOptions $options, ?string $raw) : AbstractRequest
	{
		return new Request($options, $raw);
	}

}
