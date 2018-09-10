<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $request;
	protected $response;

	public function __construct($request, $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	public function keywords(): string
	{
		return $this->request->server['request_uri'];
	}

	public function raw(): string
	{
		return $this->request->rawContent();
	}

}
