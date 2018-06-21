<?php

namespace Addons\Server\Responses;

use Google\Protobuf\Internal\Message;
use Addons\Server\Responses\RawResponse;
use Addons\Server\Contracts\AbstractRequest;

class ProtobufResponse extends RawResponse {

	public function prepare(AbstractRequest $request)
	{
		if ($this->getContent() instanceof Message)
			$this->setContent($this->getContent()->serializeToString());
	}

}
