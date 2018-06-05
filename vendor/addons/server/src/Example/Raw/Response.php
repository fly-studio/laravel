<?php

namespace Addons\Server\Example\Raw;

use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

class Response extends AbstractResponse {

	public function prepare(AbstractRequest $request): AbstractResponse
	{
		$this->body =
			date('Y-m-d H:i:s').
			' recv: '.
			$this->raw;

		return $this;
	}

}
