<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $opcode;

	public function __construct(ServerOptions $options, ?string $raw, int $opcode)
	{
		parent::__construct($options, $raw);
		$this->opcode = $opcode;
	}

	public function opcode()
	{
		return $this->opcode;
	}

	public function eigenvalue(): string
	{
		return $this->raw;
	}

}
