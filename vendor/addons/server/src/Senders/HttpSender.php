<?php

namespace Addons\Server\Senders;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractSender;

class HttpSender extends AbstractSender {

	protected $options;
	protected $request;
	protected $response;

	public function __construct(ServerOptions $options, \swoole_http_request $request, \swoole_http_response $response)
	{
		$this->options = $options;
		$this->request = $request;
		$this->response = $response;
	}

	public function options()
	{
		return $this->options;
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
		$this->response->sendfile($file);
		return $this->getLastError();
	}

	public function end(): int
	{
		return $this->end('');
	}

	protected function getLastError()
	{
		return $this->options->server()->getLastError();
	}
}
