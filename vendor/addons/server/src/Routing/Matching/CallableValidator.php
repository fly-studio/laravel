<?php

namespace Addons\Server\Routing\Matching;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;

class CallableValidator implements ValidatorInterface
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
		return call_user_func($route->eigenvalue(), $request);
	}
}
