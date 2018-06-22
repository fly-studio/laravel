<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;

class ServerProtocolValidator implements ValidatorInterface
{
	/**
	 * 匹配协议是否是TCP、UDP
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->actionExists('protocol'))
			return $route->getAction('protocol') !== $request->options()->socket_type();
		return true;
	}
}
