<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;
use Addons\Server\Listeners\Internal\TcpTrait;

class HttpListener extends AbstractListener {

	use TcpTrait;

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$binder = $this->pool->get($request->fd);
		if (empty($binder))
			return;

		$this->updateServerOptions($binder->options());

		$this->recv($binder, $request, $response);
	}

}
