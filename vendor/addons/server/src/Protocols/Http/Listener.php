<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Protocols\Http\Fire;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\Listeners\AbstractHttpListener;

class Listener extends AbstractHttpListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
