<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $raw;

	public function __construct(?string $raw)
	{
		$this->raw = $raw;
	}

	public function raw()
	{
		return $this->raw;
	}

	public function keywords(): string
	{
		return $this->raw;
	}

}
