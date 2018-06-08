<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Addons\Server\Protocols\Http\Response;
use Addons\Server\Protocols\Http\Responses\InnerResponse;

class ChunkedResponse extends Response {

	protected $callback = null;

	public function callback($callback = null)
	{
		if (is_null($callback)) return $this->callback;

		$this->callback = $callback;
		return $this;
	}

	public function send(\swoole_http_response $nativeResponse)
	{
		$this->sendMeta($nativeResponse);

		$inner = new InnerResponse($nativeResponse);

		if(!empty($this->body) || is_numeric($this->body))
			$inner->write($this->body);

		if (is_callable($this->callback))
			call_user_func($this->callback, $inner)

		$nativeResponse->end();

	}

}
