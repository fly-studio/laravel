<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;
use Addons\Server\Listeners\Internal\TcpTrait;

class TcpListener extends AbstractListener {

	use TcpTrait;

	/**
	 * [receive description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 * @param  [mixed]         $data       [description]
	 */
	public function onReceive($fd, $reactor_id, $data)
	{
		$binder = $this->pool->get($fd);
		if (empty($binder))
			return;

		$options = $binder->options();

		$this->updateServerOptions($options, $fd);

		$options->logger('info', 'TCP receive: ');
		$options->logger('debug', print_r($options->toArray(), true));
		$options->logger('hex', $data);

		$this->recv($binder, $data);
	}


}
