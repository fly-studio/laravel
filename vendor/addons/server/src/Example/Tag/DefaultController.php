<?php

namespace Addons\Server\Example\Tag;

use Illuminate\Routing\Controller;
use Addons\Server\Response\ProtobufResponse;
use Addons\Server\Example\Protobuf\SendMessage;
use Addons\Server\Protocols\TagProtobuf\Request;

class DefaultController extends Controller {

	public function reply(Request $request)
	{
		$message = new SendMessage();

		$request->options()->logger('hex', $request->body());

		return $request->attachToMessage($message) ? $message->serializeToJsonString() : null;
		//return new ProtobufResponse($otherMessage);
	}

}