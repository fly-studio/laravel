<?php

namespace Addons\Server\Example\Raw;

use Illuminate\Routing\Controller;
use Addons\Server\Example\Raw\Response;
use Addons\Server\Protocols\Raw\Request;
use Addons\Server\Contracts\AbstractService;

class DefaultController extends Controller {

	public function reply(Request $request)
	{
		return new Response($request->options(), $request->raw());
	}

}
