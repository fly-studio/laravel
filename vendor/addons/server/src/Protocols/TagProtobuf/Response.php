<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Google\Protobuf\Internal\Message;
use Addons\Server\Responses\RawResponse;
use Addons\Server\Senders\WebSocketSender;
use Addons\Server\Contracts\AbstractRequest;

class Response extends RawResponse {

	protected $protocol = null;

	public function __construct($protocol, $content)
	{
		if (is_numeric($protocol))
			$this->protocol = pack('n', $protocol);
		else if (empty($protocol))
			$this->protocol = "\x0\x0";
		else
			$this->protocol = substr($protocol, 0, 2);

		$this->content = $content;
	}

	public function prepare(AbstractRequest $request)
	{
		$content = $this->getContent() instanceof Message ? $this->getContent()->serializeToString() : $this->getContent();

		$this->setContent($this->protocol. pack('N', strlen($content)) .$content);
		//hex_dump($this->content);

		return $this;
	}

}
