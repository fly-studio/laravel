<?php

namespace Addons\Server\Senders;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractSender;

class WebSocketSender extends AbstractSender {

	protected $buffer_output_size;

	public function __construct(ConnectBinder $binder)
	{
		$this->binder = $binder;
		$this->buffer_output_size = $binder->options()->server()->setting['buffer_output_size'];
	}

	public function send(string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): int
	{
		$options = $this->options();

		if (($len = strlen($data)) > $this->buffer_output_size)
		{
			for($i = 0; $i < ceil($len / $this->buffer_output_size); ++$i)
				$options->server()->push($options->file_descriptor(), substr($data, $i * $this->buffer_output_size, $this->buffer_output_size), false);
			$options->server()->push($options->file_descriptor(), '', $opcode, $finish);
		} else {
			$options->server()->push($options->file_descriptor(), $data, $opcode, $finish);
		}
		return $this->getLastError();
	}

	public function chunk(string $data, int $opcode = WEBSOCKET_OPCODE_TEXT): int
	{
		return $this->send($data, $opcode);
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
				$this->send($data = fread($fp, $this->buffer_output_size), $opcode, false);
				$len += strlen($data);
			}
			fclose($fp);
		} else {
			$this->send(file_get_contents($path, false, null, $offset, $length), $opcode, true);
		}
		$this->end($opcode);
		return $this->getLastError();
	}

	public function end(int $opcode = WEBSOCKET_OPCODE_TEXT): int
	{
		return $this->send('', $opcode);
	}

	protected function getLastError()
	{
		return $this->options()->server()->getLastError();
	}
}
