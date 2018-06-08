<?php

namespace Addons\Server\Example\GRPC;

use Illuminate\Routing\Controller;
use Addons\Server\Protocols\GRPC\Request;
use Addons\Server\Protocols\GRPC\Response;

class DefaultController extends Controller {

	public function reply(Request $request)
	{

		$message = new \Addons\Server\Example\Protobuf\ReplyMessage();
		$message->setContent(dd_r($request->options()->server()->nativeServer(), true, 'html'));
		$request->options()->logger('hex', print_r($request->raw(), true));
		return new Response($request->options(), $message);
	}

}
