<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Protocols\Raw\Request;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Contracts\AbstractRequest;

class Protocol extends AbstractProtocol {

	public function decode(ConnectBinder $binder, ...$args) : AbstractRequest
	{
		return new Request(...$args);
	}

}
