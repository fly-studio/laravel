<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Google\Protobuf\Internal\Message;
use Addons\Server\Responses\RawResponse;
use Addons\Core\Contracts\Protobufable;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Protocols\TagProtobuf\Request;
use Addons\Server\Protocols\TagProtobuf\Response;

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

	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		if ($response instanceof AbstractResponse)
			$response = $response;
		else if ($response instanceof Protobufable)
			$response = new Response(null, $response->toProtobuf());
		else if ($response instanceof Message)
			$response = new Response(null, $response);
		else if (empty($response) && !is_numeric($response))
			return null;
		else
			$response = new RawResponse(@strval($response));

		return $response;
	}

}
