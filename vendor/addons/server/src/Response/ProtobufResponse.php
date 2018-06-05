<?php

namespace Addons\Server\Response;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractResponse;

class ProtobufResponse extends AbstractResponse {

	protected function prepare()
	{
		if ($this->content instanceof Message)
			return $this->content->serializeToJsonString();
		else
			return $this->content;
	}

}
