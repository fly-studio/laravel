<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;

class RawValidator implements ValidatorInterface
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
		return $request->eigenvalue() === $route->eigenvalue();
	}
}
