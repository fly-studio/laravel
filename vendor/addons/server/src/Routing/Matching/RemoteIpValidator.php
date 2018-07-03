<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class RemoteIpValidator implements RouteValidatorInterface
{
	/**
	 * 匹配客户端IP
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->actionExists('ip'))
			return $route->getAction('ip') !== $request->options()->client_ip();
		return true;
	}
}
