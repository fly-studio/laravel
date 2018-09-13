<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use RuntimeException;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractResponse;

class Response extends AbstractResponse {

	protected $headers = [];
	protected $cookies = [];
	protected $status = 200;
	protected $gzip = null;

	public function status(int $status = null)
	{
		if (is_null($status)) return $this->status;

		$this->status = $status;
		return $this;
	}

	public function gzip(int $gzip = null)
	{
		if (is_null($gzip)) return $this->gzip;

		$this->gzip = $gzip;
		return $this;
	}

	public function header(string $key, string $value)
	{
		$this->headers[$key] = $value;
		return $this;
	}

	public function cookie(string $key, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
	{
		$this->cookies[$key] = compact('value', 'expire', 'path', 'domain', 'secure', 'httponly');
		return $this;
	}

	protected function sendMeta()
	{
		$nativeResponse = $this->sender->response();
		foreach($this->headers as $k => $v)
			$nativeResponse->header($k, ...array_values(array_wrap($v)));

		foreach($this->cookies as $k => $v)
			$nativeResponse->cookie($k, ...array_values(array_wrap($v)));

		$nativeResponse->status($this->status);

		if (!is_null($this->gzip)) $nativeResponse->gzip($this->gzip);
	}

	public function send()
	{
		static $i;
		if (empty($i)) $i =0;

		echo ++$i, PHP_EOL;

		$this->sendMeta();

		$this->sender->send($this->getContent());
	}

}
