<?php

namespace Addons\Server\Protocols\Http\Responses;

use Addons\Server\Protocols\Http\Response;

class RedirectResponse extends Response {

	protected $url = null;
	protected $http_code = null;

	public function redirect(string $url, int $http_code = 302)
	{
		$this->url = $url;
		$this->http_code = $http_code;
		return $this;
	}

	public function send(\swoole_http_response $nativeResponse)
	{
		return $nativeResponse->redirect($this->url, $this->http_code);
	}
}
