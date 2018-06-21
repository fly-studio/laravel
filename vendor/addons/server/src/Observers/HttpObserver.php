<?php

namespace Addons\Server\Observers;

use Addons\Server\Observers\Observer;
use Addons\Server\Contracts\Listeners\AbstractHttpListener;

class HttpObserver extends Observer {

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$this->trigger('onRequest', $request, $response);
	}

}
