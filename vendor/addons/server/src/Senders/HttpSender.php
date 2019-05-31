<?php

namespace Addons\Server\Senders;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractSender;

class HttpSender extends AbstractSender {

	protected $request;
	protected $response;

	public function __construct(ConnectBinder $binder, \swoole_http_request $request, \swoole_http_response $response)
	{
		$this->binder = $binder;
		$this->request = $request;
		$this->response = $response;
	}

	public function request()
	{
		return $this->request;
	}

	public function response()
	{
		return $this->response;
	}

	public function redirect(string $url, $status_code = 302): int
	{
		$this->response->redirect($url, $status_code);
		return $this->getLastError();
	}

	public function send(string $data): int
	{
		$this->response->end($data);
		return $this->getLastError();
	}

	public function chunk(string $data): int
	{
		$this->response->write($data);
		return $this->getLastError();
	}

	public function file(string $path, int $offset = -1, int $maxlen = null): int
	{
		$this->response->sendfile($path);
		return $this->getLastError();
	}

	public function end(): int
	{
		return $this->response->end('');
	}

	protected function getLastError()
	{
		return $this->options()->server()->getLastError();
	}
}
