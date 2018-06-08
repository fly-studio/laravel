<?php

namespace Addons\Server\Protocols\Http\Responses;

use BadMethodCallException;

class InnerResponse {

	private $nativeResponse;

	public function __construct(\swoole_http_response $nativeResponse)
	{
		$this->nativeResponse = $nativeResponse;
	}

	public function __call($method, $args)
	{
		if (method_exists($this->nativeResponse, $method))
			return $this->nativeResponse->$method(...$args);

		throw new BadMethodCallException(sprintf(
			'Method %s::%s does not exist.', static::class, $method
		));
	}
}
