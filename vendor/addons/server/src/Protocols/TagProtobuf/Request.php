<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $protocol = null;
	protected $body = null;

	public function __construct($protocol, $body)
	{
		$this->protocol = $protocol;
		$this->body = $body;
	}

	public function protocol()
	{
		return $this->protocol;
	}

	public function body()
	{
		return $this->body;
	}

	public function keywords(): string
	{
		return $this->protocol;
	}

	public function attachToMessage(Message $message)
	{
		if (empty($this->body))
			return false;

		try {
			$message->mergeFromString($this->body);
			return true;

		} catch(\Exception $e) {

			return false;
		}
	}

}
