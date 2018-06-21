<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;

class BufferListener extends AbstractListener {

	/**
	 * [bufferFull description]
	 * @param  [int]         $fd     [description]
	 */
	public function onBufferFull($fd)
	{

	}

	/**
	 * [bufferEmpty description]
	 * @param  [int]         $fd   [description]
	 */
	public function onBufferEmpty($fd)
	{

	}
}
