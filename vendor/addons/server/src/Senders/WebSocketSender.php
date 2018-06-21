<?php

namespace Addons\Server\Senders;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractSender;

class WebSocketSender extends AbstractSender {

	protected $options;
	protected $buffer_output_size;

	public function __construct(ServerOptions $options)
	{
		$this->options = $options;
		$this->buffer_output_size = $options->server()->setting['buffer_output_size'];
	}

	public function options()
	{
		return $this->options;
	}

	public function send(string $data, int $opcode = WEBSOCKET_OPCODE_TEXT): int
	{
		if (($len = strlen($data)) > $this->buffer_output_size)
		{
			for($i = 0; $i < ceil($len / $this->buffer_output_size); ++$i)
				$this->options->server()->push($this->options->file_descriptor(), substr($data, $i * $this->buffer_output_size, $this->buffer_output_size));
		} else {
			$this->options->server()->push($this->options->file_descriptor(), $data);
		}
		return $this->getLastError();
	}

	public function chunk(string $data, int $opcode = WEBSOCKET_OPCODE_TEXT): int
	{
		return $this->send($this->options, $data, $opcode);
	}

	public function file(string $path, int $opcode = WEBSOCKET_OPCODE_TEXT, ?int $offset = 0, int $length = null): int
	{
		$size = filesize($path);
		$length = $length > 0 ? $length : $size - $offset;

		if ($length > $this->buffer_output_size)
		{
			$fp = fopen($path, 'rb');
			fseek($fp, $offset);
			$len = 0;
			while(!feof($fp) && $len <= $length)
			{
				$this->send($data = fread($fp, $this->buffer_output_size), $opcode);
				$len += strlen($data);
			}
			fclose($fp);
		} else {
			$this->send(file_get_contents($path, false, null, $offset, $length), $opcode);
		}

		return $this->getLastError();
	}

	protected function getLastError()
	{
		return $this->options->server()->getLastError();
	}
}
