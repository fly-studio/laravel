<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Addons\Func\Structs\WrapperClass;
use Addons\Server\Protocols\Http\Response;

class ChunkedResponse extends Response {

	protected $callback = null;

	public function callback(callable $callback = null)
	{
		if (is_null($callback)) return $this->callback;

		$this->callback = $callback;
		return $this;
	}

	public function send(\swoole_http_response $nativeResponse)
	{
		$this->sendMeta($nativeResponse);

		$inner = new WrapperClass($nativeResponse);

		if(!empty($this->content) || is_numeric($this->content))
			$inner->write($this->content);

		if (is_callable($this->callback))
			call_user_func($this->callback, $inner);

		$nativeResponse->end();
	}

}
