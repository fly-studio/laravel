<?php

namespace Addons\Server\Example\Raw;

use Addons\Server\Contracts\AbstractRequest;

class Response extends AbstractResponse {

	public function prepare(AbstractRequest $request)
	{
		$this->body =
			date('Y-m-d H:i:s').
			' recv: '.
			$this->content;

		return $this;
	}

}
