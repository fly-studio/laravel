<?php

namespace Addons\Server\Senders;

use Addons\Server\Senders\Sender;

class UdpSender extends Sender {

	protected $buffer_output_size = 65507;

	public function send(string $data): int
	{
		if (($len = strlen($data)) > $this->buffer_output_size)
		{
			for($i = 0; $i < ceil($len / $this->buffer_output_size); ++$i)
				$this->sendUdp(substr($data, $i * $this->buffer_output_size, $this->buffer_output_size));
		} else {
			$this->sendUdp($data);
		}
		return $this->getLastError();
	}

	public function file(string $path, int $offset = 0, int $length = null): int
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
				$this->send($data = fread($fp, $this->buffer_output_size));
				$len += strlen($data);
			}
			fclose($fp);
		} else {
			$this->send(file_get_contents($path, false, null, $offset, $length));
		}

		$this->end();

		return $this->getLastError();
	}
}
