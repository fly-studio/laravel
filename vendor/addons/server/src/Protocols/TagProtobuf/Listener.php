<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Protocols\TagProtobuf\Request;
use Addons\Server\Protocols\TagProtobuf\Response;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	public function doRequest(ServerOptions $options, $raw) : AbstractRequest
	{
		if (strlen($raw) <= 6)
			return false;

		$protocol = substr($raw, 0, 2);
		list(, $length) = unpack('N', substr($raw, 2, 4));

		if (strlen($raw) != $length + 6)
			return false;

		$data = substr($raw, 6);

		return new Request($options, $service, $data);
	}

	public function doResponse(ServerOptions $options, AbstractRequest $request, string $raw) : AbstractResponse
	{
		return Response::buildFromRequest($request);
	}

}
