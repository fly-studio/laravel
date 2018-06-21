<?php

namespace Addons\Server\Example\Raw;

use Addons\Server\Responses\RawResponse;
use Addons\Server\Contracts\AbstractRequest;

class Response extends RawResponse {

	public function prepare(AbstractRequest $request)
	{
		$this->content =
			date('Y-m-d H:i:s').
			' recv: '.
			$this->content;

		return $this;
	}

}
