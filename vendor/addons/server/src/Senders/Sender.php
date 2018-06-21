<?php

namespace Addons\Server\Senders;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractSender;

abstract class Sender extends AbstractSender {

	protected $options;

	public function __construct(ServerOptions $options)
	{
		$this->options = $options;
	}

	public function options()
	{
		return $this->options;
	}

	public function sendTcp(string $data)
	{
		$this->options->logger('info', 'TCP reply: ', 'send');
		$this->options->logger('hex', $data);

		return $this->options->server()->send($this->options->file_descriptor(), $data);
	}

	public function sendUdp(string $data)
	{
		$this->options->logger('info', 'UDP reply: ', 'send');
		$this->options->logger('hex', $data);

		list(, $fd) = unpack('L', pack('N', ip2long($this->options->client_ip())));
		$reactor_id = ($this->options->server_socket() << 16) + $this->options->client_port();

		return $this->options->server()->send($fd, $data, $reactor_id);
	}

	public function chunk(string $data): int
	{
		return $this->send($data);
	}

	protected function getLastError()
	{
		return $this->options->server()->getLastError();
	}

}
