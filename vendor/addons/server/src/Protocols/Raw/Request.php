<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected function parseHeader(?string $raw)
	{
		return null;
	}

	protected function parseBody(?string $raw)
	{
		return $raw;
	}

	public function eigenvalue(): string
	{
		return $this->body;
	}

}
