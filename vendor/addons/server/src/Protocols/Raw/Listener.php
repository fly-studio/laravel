<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Protocols\Raw\Fire;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
