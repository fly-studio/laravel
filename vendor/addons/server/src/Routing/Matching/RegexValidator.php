<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\RouteValidatorInterface;

class RegexValidator implements RouteValidatorInterface
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
		if (!empty($route->compiled))
			return preg_match($route->compiled->getRegex(), $request->keywords());
		return true;
	}
}
