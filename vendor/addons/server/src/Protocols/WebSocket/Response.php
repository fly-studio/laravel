<?php

namespace Addons\Server\Protocols\WebSocket;

use Closure;
use RuntimeException;
use Addons\Server\Contracts\AbstractResponse;

class Response extends AbstractResponse {

	protected $opcode;

	public function __construct(?string $content, int $opcode)
	{
		parent:__construct($content);
		$this->opcode = $opcode;
	}

	public function opcode()
	{
		return $this->opcode;
	}

	public function push(string $data, int $opcode)
	{
		$this->server->nativeServer()->push($this->options()->file_descriptor(), $data, $opcode);
	}

	public function send()
	{
		$this->throwNullServerOptions();

		$this->push($this->content, $this->opcode);
	}

}
