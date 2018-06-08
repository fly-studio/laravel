<?php

namespace Addons\Server\Protocols\Grpc;

use Google\Protobuf\Internal\Message;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Protocols\Http\Response as HttpResponse;

class Response extends HttpResponse {

	protected $headers = [
		'grpc-status' => [0, false]
	];

	public function prepare(AbstractRequest $request)
	{
		if ($this->getContent() instanceof Message)
			$this->setContent($this->getContent()->serializeToString());
	}

}
