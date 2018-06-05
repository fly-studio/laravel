<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Protocols\TagProtobuf\Request;

class Fire extends AbstractFire {

	public function analyzing(ServerOptions $options, ?string $raw) : AbstractRequest
	{
		return new Request($options, $raw);
	}

}
