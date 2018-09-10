<?php

namespace Addons\Server\Example\Grpc;

use Illuminate\Routing\Controller;
use Addons\Server\Protocols\Grpc\Request;
use Addons\Server\Protocols\Grpc\Response;

class DefaultController extends Controller {

	public function reply(Request $request)
	{
		$message = new \Addons\Server\Example\Protobuf\ReplyMessage();
		$message->setContent(dd_r($request->options()->server()->nativeServer(), true, 'html'));
		$request->options()->logger('hex', print_r($request->raw(), true));
		
		return new Response($message);
	}

}
