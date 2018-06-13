<?php

namespace Addons\Server\Protocols\WebSocket\Responses;

use Closure;
use Addons\Func\Structs\WrapperClass;
use Addons\Server\Protocols\WebSocket\Response;

class ChunkedResponse extends Response {

	protected $callback = null;

	public function callback(callable $callback = null)
	{
		if (is_null($callback)) return $this->callback;

		$this->callback = $callback;
		return $this;
	}

	public function send()
	{
		$inner = new WrapperClass($this);

		if(!empty($this->content) || is_numeric($this->content))
			$inner->push($this->content, $this->opcode);

		if (is_callable($this->callback))
			call_user_func($this->callback, $inner);

		$nativeResponse->end();
	}

}
