<?php

namespace Addons\Server\Protocols;

use Addons\Server\Contracts\AbstractResponse;

class CloseResponse extends AbstractResponse {

	protected function bootCloseResponse()
	{
		$this->nextAction([$this, 'closeConnect']);
	}

	protected function closeConnect()
	{
		if ($this->options->socket_type() == SWOOLE_SOCK_TCP)
			return $this->options->server()->close($this->options->file_descriptor());
	}

	public function reply() : ?array {
		return null;
	}

}
