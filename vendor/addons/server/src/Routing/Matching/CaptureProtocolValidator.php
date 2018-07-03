<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class CaptureProtocolValidator implements RouteValidatorInterface
{
	/**
	 * 上层协议是否匹配
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->actionExists('capture'))
			return is_subclass_of($route->getAction('capture'), get_class($request->capture()));
		return true;
	}
}
