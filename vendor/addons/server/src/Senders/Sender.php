<?php

namespace Addons\Server\Senders;

use Addons\Server\Contracts\AbstractSender;

abstract class Sender extends AbstractSender {

	public function sendTcp(string $data)
	{
		$options = $this->options();

		$options->logger('info', 'TCP reply: ', 'send');
		$options->logger('hex', $data);

		return $options->server()->send($options->file_descriptor(), $data);
	}

	public function sendUdp(string $data)
	{
		$options = $this->options();

		$options->logger('info', 'UDP reply: ', 'send');
		$options->logger('hex', $data);

		list(, $fd) = unpack('L', pack('N', ip2long($options->client_ip())));
		$reactor_id = ($options->server_socket() << 16) + $options->client_port();

		return $options->server()->send($fd, $data, $reactor_id);
	}

	public function chunk(string $data): int
	{
		return $this->send($data);
	}

	public function end(): int
	{
		return 0;
	}

	protected function getLastError()
	{
		return $this->options()->server()->getLastError();
	}

}
