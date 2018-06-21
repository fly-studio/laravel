<?php

namespace Addons\Server\Responses;

use Addons\Server\Contracts\AbstractResponse;

trait CloseTrait {

	protected function bootCloseTrait()
	{
		//To Do
		$this->addListener('destroy', [$this, 'closeConnect']);
	}

	protected function closeConnect(AbstractResponse $response)
	{
		if ($response->options()->socket_type() == SWOOLE_SOCK_TCP)
			return $response->options()->server()->close($response->options()->file_descriptor());
	}

}
