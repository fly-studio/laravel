<?php

namespace Addons\Server\Protocols\TagV2;

use Google\Protobuf\Internal\Message;
use Addons\Server\Responses\RawResponse;
use Addons\Core\Contracts\Protobufable;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Protocols\TagV2\Request;
use Addons\Server\Protocols\TagV2\Response;

class Protocol extends AbstractProtocol {

	use PackageOffsetTrait;

	// | 2 bytes  | 2 bytes      | 2 bytes  | 4 bytes             | content |
	// | ack (BE) | version (BE) | protocol | content length (BE) |         |

	public function decode(ConnectBinder $binder , ...$args) : ?AbstractRequest
	{
		$raw = $args[0];

		if (is_null($raw))
			return null;

		if (strlen($raw) < 10)
			throw new \Exception('RAW size < 10');

		list(, $ack) = unpack('n', substr($raw, 0, 2));
		list(, $version) = unpack('n', substr($raw, 2, 2));
		$protocol = substr($raw, 4, 2);
		list(, $length) = unpack('N', substr($raw, 6, 4));

		if (strlen($raw) != $length + 10)
			throw new \Exception('RAW is incomplete.');

		return new Request($ack, $version, $protocol, substr($raw, 10));
	}

	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		if (!($request instanceof Request))
			return null;

		if ($response instanceof AbstractResponse)
			$response = $response;
		else if ($response instanceof Protobufable)
			$response = new Response($request->ack(), $request->version(), $request->protocol(), $response->toProtobuf());
		else if ($response instanceof Message)
			$response = new Response($request->ack(), $request->version(), $request->protocol(), $response);
		else if (empty($response) && !is_numeric($response))
			return null;
		else
			$response = new Response($request->ack(), $request->version(), $request->protocol(), @strval($response));

		return $response;
	}

}
