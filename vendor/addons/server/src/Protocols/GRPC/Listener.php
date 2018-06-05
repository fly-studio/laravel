<?php

namespace Addons\Server\Protocols\GPRC;

use Addons\Server\Protocols\GPRC\Fire;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
