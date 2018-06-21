<?php

namespace Addons\Server\Responses;

use Addons\Server\Contracts\AbstractResponse;

class RawResponse extends AbstractResponse {

	public function send()
	{
		$data = $this->content;
		if (empty($data) && !is_numeric($data))
			return;

		$this->sender->send($data);
	}

}
