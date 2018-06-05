<?php

namespace Addons\Server\Response;

use Addons\Server\Contracts\AbstractResponse;

class CloseResponse extends AbstractResponse {

	protected function bootCloseResponse()
	{
		$this->nextAction([$this, 'closeConnect']);
	}

	protected function closeConnect(AbstractResponse $response)
	{
		if ($response->options()->socket_type() == SWOOLE_SOCK_TCP)
			return $response->server()->close($response->options()->file_descriptor());
	}

}
