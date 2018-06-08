<?php

namespace Addons\Server\Response;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

class ProtobufResponse extends AbstractResponse {

	public function prepare(AbstractRequest $request)
	{
		if ($this->getContent() instanceof Message)
			$this->setContent($this->getContent()->serializeToString());
	}

}
