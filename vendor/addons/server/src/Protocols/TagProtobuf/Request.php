<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $protocol = null;
	protected $body = null;

	protected function parse(?string $raw)
	{
		if (is_null($raw))
			return;

		if (strlen($raw) <= 6)
			throw new \Exception('RAW size <= 6');

		$this->protocol = substr($raw, 0, 2);
		list(, $length) = unpack('N', substr($raw, 2, 4));

		if (strlen($raw) != $length + 6)
			throw new \Exception('RAW is incomplete.');

		$this->body = substr($raw, 6);

		return null;
	}

	public function protocol()
	{
		return $this->protocol;
	}

	public function body()
	{
		return $this->body;
	}

	public function eigenvalue(): string
	{
		return $this->protocol;
	}

	public function attachToMessage(Message $message)
	{
		try {
			$message->mergeFromString($this->body);
			return true;
		} catch(\Exception $e) {
			return false;
		}
	}

}
