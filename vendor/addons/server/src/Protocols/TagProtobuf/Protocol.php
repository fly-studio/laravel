<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Protocols\TagProtobuf\Request;

class Protocol extends AbstractProtocol {

	public function decode(ServerOptions $options , ...$args) : ?AbstractRequest
	{
		$raw = $args[0];

		if (is_null($raw))
			return null;

		if (strlen($raw) <= 6)
			throw new \Exception('RAW size <= 6');

		$protocol = substr($raw, 0, 2);
		list(, $length) = unpack('N', substr($raw, 2, 4));

		if (strlen($raw) != $length + 6)
			throw new \Exception('RAW is incomplete.');

		return new Request($protocol, substr($raw, 6));
	}

}
