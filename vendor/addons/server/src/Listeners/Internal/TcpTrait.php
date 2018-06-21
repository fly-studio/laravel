<?php

namespace Addons\Server\Listeners\Internal;

use Addons\Server\Listeners\Internal\OptionsTrait;

trait TcpTrait {

	use OptionsTrait;

	/**
	 * [connect description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onConnect($fd, $reactor_id)
	{
		$options = $this->makeServerOptions($fd);

		$this->pool->set($options->unique(), $options);

		$options->logger('info', sprintf('TCP Server connect from [%s:%s]', $options->client_ip(), $options->client_port()));
	}

	/**
	 * [close description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onClose($fd, $reactor_id)
	{
		//Only TCP
		$this->pool->remove($fd);
	}
}
