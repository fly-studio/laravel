<?php

namespace Addons\Server\Protocols\Grpc;

use Addons\Server\Protocols\Grpc\Fire;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Protocols\Http\Listener as HttpListener;

class Listener extends HttpListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
