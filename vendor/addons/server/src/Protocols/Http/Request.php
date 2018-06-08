<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $nativeRequest = null;

	public function __construct(ServerOptions $options, \swoole_http_request $nativeRequest)
	{
		$this->options = $options;
		$this->nativeRequest = $nativeRequest;
		$this->raw = $nativeRequest->getData();
	}

	public function body()
	{
		return property_exists($this->nativeRequest, 'rawContent') ? $this->nativeRequest->rawContent : null;
	}

	public function nativeRequest() : \swoole_http_request
	{
		return $this->nativeRequest;
	}

	public function eigenvalue(): string
	{
		return $this->nativeRequest->server['request_uri'];
	}

}
