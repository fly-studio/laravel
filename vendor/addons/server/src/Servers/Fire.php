<?php

namespace Addons\Server\Servers;

use Closure;
use Addons\Server\Contracts\IFire;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

class Fire implements IFire {

	protected $options;
	protected $requestCallback;
	protected $responseCallback;

	public function __construct(ServerOptions $options, $requestCallback, $responseCallback)
	{
		$this->options = $options;
		$this->requestCallback = $requestCallback;
		$this->responseCallback = $responseCallback;
		if (!is_callable($requestCallback) || !is_callable($responseCallback))
			throw new RuntimeException('$requestCallback, $responseCallback must be callable.');
	}

	public function doRequest(ServerOptions $options, ?string $raw): AbstractRequest
	{
		return call_user_func($this->requestCallback, $options, $raw);
	}

	public function doResponse(ServerOptions $options, AbstractRequest $request, ?string $raw): AbstractResponse
	{
		return call_user_func($this->responseCallback, $options, $request, $raw);
	}

	protected function sendUDP(string $_data)
	{
		$this->options->logger('info', 'UDP reply: ', 'send');
		$this->options->logger('hex', $_data);
		list(, $fd) = unpack('L', pack('N', ip2long($this->options->client_ip())));
		$reactor_id = ($this->options->server_socket() << 16) + $this->options->client_port();

		return $this->options->server()->send($fd, $_data, $reactor_id);
	}

	protected function sendTCP(string $_data) {
		$this->options->logger('info', 'TCP reply: ', 'send');
		$this->options->logger('hex', $_data);
		return $this->options->server()->send($this->options->file_descriptor(), $_data);
	}

	public function handle(?string $raw)
	{
		try {
			$request = $this->doRequest($this->options, $raw);
			$response = $this->doResponse($this->options, $request, $raw);

			if (!empty($response))
			{
				$reply = $response->reply();
				if (!empty($reply))
				{
					foreach(array_wrap($reply) as $_data) {
						switch($this->options->socket_type())
						{
							case SWOOLE_SOCK_UDP:
								$this->sendUDP($_data);
								break;
							case SWOOLE_SOCK_TCP:
								$this->sendTCP($_data);
								break;
						}
					}
				}
				if (is_callable($response->nextAction()))
					call_user_func($response->nextAction(), $this->options, $request, $response, $reply);
			}
		} catch (Exception $e) {
			$this->options->logger('error', $e->getMessage());
		}

	}
}
