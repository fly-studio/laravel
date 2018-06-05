<?php

namespace Addons\Server\Response;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractRequest;

class ProtobufResponse extends AbstractResponse {

	public function prepare(AbstractRequest $request)
	{
		$this->body = $this->content instanceof Message ? $this->content->serializeToString() : $this->content;
	}

}
