<?php

namespace Addons\Server\Protocols\Raw;

use Addons\Server\Console\ConsoleLog;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Raw\Request;
use Addons\Server\Protocols\Raw\Response;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Listener extends AbstractProtocolListener {

	public function doRequest(ServerOptions $options, ?string $raw ) : AbstractRequest
	{
		return new Request($options, null, $raw);
	}

	public function doResponse(ServerOptions $options, AbstractRequest $request, ?string $raw) : AbstractResponse
	{
		return Response::buildFromRequest($request);
	}

}
