<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected function parse(?string $raw)
	{

	}

	public function eigenvalue(): string
	{
		return $this->raw;
	}

}
