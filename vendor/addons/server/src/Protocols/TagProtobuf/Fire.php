<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractFire;
use Addons\Server\Contracts\AbstractService;
use Addons\Server\Contracts\AbstractResponse;

class Fire extends AbstractFire {

	public function analyzing(ServerOptions $options, ?string $raw) : ?AbstractService
	{
		if (strlen($raw) <= 6)
			throw new \Exception('RAW size <= 6');

		$protocol = substr($raw, 0, 2);
		list(, $length) = unpack('N', substr($raw, 2, 4));

		if (strlen($raw) != $length + 6)
			throw new \Exception('RAW size is invalid.');

		$data = substr($raw, 6);

		return $this->makeService($protocol, $options, $data);
	}

}
