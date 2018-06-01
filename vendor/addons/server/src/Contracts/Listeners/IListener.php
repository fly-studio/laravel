<?php

namespace Addons\Server\Contracts\Listeners;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

interface IListener {
	function doRequest(ServerOptions $options, ?string $raw): AbstractRequest;
	function doResponse(ServerOptions $options, AbstractRequest $request, ?string $raw): AbstractResponse;
}
