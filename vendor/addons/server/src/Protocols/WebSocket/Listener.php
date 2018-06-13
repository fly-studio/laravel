<?php

namespace Addons\Server\Protocols\WebSocket;

use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Protocols\WebSocket\Fire;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
