<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Contracts\AbstractResponse;

class Response extends AbstractResponse {

	public function reply() : ?array {
		return [
			'recv',
			$this->data(),
		];
	}

}
