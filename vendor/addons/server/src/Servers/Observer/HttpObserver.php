<?php

namespace Addons\Server\Servers\Observer;

use Addons\Server\Servers\Observer\Observer;
use Addons\Server\Contracts\Listeners\AbstractHttpListener;

class HttpObserver extends Observer {

	public function setProtocolListener(AbstractHttpListener $listener)
	{
		$this->listener = $listener;
	}

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$this->listener->onRequest($request, $response);
	}

}
