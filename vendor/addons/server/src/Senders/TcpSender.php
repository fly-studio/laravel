<?php

namespace Addons\Server\Senders;

use Addons\Server\Senders\Sender;
use Addons\Server\Structs\ServerOptions;

class TcpSender extends Sender {

	protected $buffer_output_size;

	public function __construct(ServerOptions $options)
	{
		$this->options = $options;
		$this->buffer_output_size = $options->server()->setting['buffer_output_size'];
	}

	public function send(string $data): int
	{
		if (($len = strlen($data)) > $this->buffer_output_size)
		{
			for($i = 0; $i < ceil($len / $this->buffer_output_size); ++$i)
				$this->sendTcp(substr($data, $i * $this->buffer_output_size, $this->buffer_output_size));
		} else {
			$this->sendTcp($data);
		}
		return $this->getLastError();
	}

	public function file(string $path, int $offset = 0, int $length = null): int
	{
		$this->options->server()->sendfile($this->options->file_descriptor(), $path, $offset, $length);
		return $this->getLastError();
	}
}
