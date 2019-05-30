<?php

namespace Addons\Server\Protocols\Http\Responses;

use Addons\Server\Protocols\Http\Responses\Response;

class RedirectResponse extends Response {

	protected $url = null;
	protected $http_code = null;

	public function __construct(string $url, int $http_code = 302)
	{
		$this->url = $url;
		$this->http_code = $http_code;
	}

	public function send()
	{
		$this->sendMeta();

		return $this->sender->redirect($this->url, $this->http_code);
	}
}
