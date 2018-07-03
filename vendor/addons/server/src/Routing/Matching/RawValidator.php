<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class RawValidator implements RouteValidatorInterface
{
	/**
	 * 原始内容是否匹配
	 *
	 * @param  Route           $route
	 * @param  AbstractRequest $request
	 * @return bool
	 */
	public function matches(Route $route, AbstractRequest $request): bool
	{
		if ($route->getType() == Route::TYPE_RAW)
			return $request->keywords() === $route->getPattern();
		return true;
	}
}
