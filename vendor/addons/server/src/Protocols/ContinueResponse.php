<?php

namespace Addons\Server\Protocols;

use Addons\Server\Contracts\AbstractResponse;

class ContinueResponse extends AbstractResponse {

	public function reply() : ?array {
		return null;
	}

}
