<?php

namespace Addons\Server\Example\Tag;

use Illuminate\Routing\Controller;
use Addons\Server\Example\Protobuf\SendMessage;
use Addons\Server\Protocols\TagProtobuf\Request;
use Addons\Server\Protocols\TagProtobuf\Response;

class DefaultController extends Controller {

	public function reply(Request $request)
	{
		$message = new SendMessage();

		//$request->options()->logger('hex', $request->body());

		return new Response($request->protocol(), $request->attachToMessage($message) ? $message->serializeToJsonString() : 'Unknow protobuf');
	}

}
