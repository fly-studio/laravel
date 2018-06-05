<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Protocols\TagProtobuf\Fire;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	protected function makeFire(): AbstractFire
	{
		return new Fire($this->server);
	}

}
