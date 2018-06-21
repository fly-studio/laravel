<?php

namespace Addons\Server\Responses;

use Addons\Server\Senders\WebSocketSender;
use Addons\Server\Contracts\AbstractResponse;

class RawResponse extends AbstractResponse {

	public function send()
	{
		$data = $this->getContent();
		if (empty($data) && !is_numeric($data))
			return;
		$this->sender->send($data, $this->sender instanceof WebSocketSender ? WEBSOCKET_OPCODE_BINARY : null);
	}

}
