<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	public function eigenvalue(): string
	{
		return $this->raw;
	}

}
