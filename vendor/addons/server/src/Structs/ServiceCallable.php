<?php

namespace Addons\Server\Structs;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

class ServiceCallable {

	protected $method;

	public function __construct(AbstractService $service, ?string $method)
	{
		$this->method = $method ?? 'handle';
	}

	public function call(ServerOptions $options, AbstractRequest $request, ...$args)
	{
		
	}
}
