<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class ServerPortValidator implements RouteValidatorInterface
{
	/**
	 * 匹配服务端口
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->actionExists('port'))
			return $route->getAction('port') !== $request->options()->server_port();
		return true;
	}
}
