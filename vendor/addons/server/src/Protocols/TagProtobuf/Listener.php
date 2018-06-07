<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Protocols\TagProtobuf\Fire;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	protected function makeFire(): AbstractFire
	{
		$this->server->set([
			'open_length_check' => true,
			'package_length_type' => 'N',
			'package_length_offset' => 2,
			'package_body_offset' => 6,
		]);
		return new Fire($this->server);
	}

}
