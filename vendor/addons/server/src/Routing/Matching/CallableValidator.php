<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class CallableValidator implements RouteValidatorInterface
{
	/**
	 * 执行callback之后是否匹配
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->getType() == Route::TYPE_CALL)
			return call_user_func($route->getPattern(), $request);
		return true;
	}
}
