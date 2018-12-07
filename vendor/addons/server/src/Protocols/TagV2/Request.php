<?php

namespace Addons\Server\Protocols\TagV2;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractRequest;

class Request extends AbstractRequest {

	protected $ack = null;
	protected $version = null;
	protected $protocol = null;
	protected $body = null;

	public function __construct($ack, $version, $protocol, $body)
	{
		$this->ack = $ack;
		$this->version = $version;
		$this->protocol = $protocol;
		$this->body = $body;
	}

	public function protocol()
	{
		return $this->protocol;
	}

	public function ack()
	{
		return $this->ack;
	}

	public function version()
	{
		return $this->version;
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
