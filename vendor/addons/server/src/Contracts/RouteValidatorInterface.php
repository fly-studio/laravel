<?php

namespace Addons\Server\Contracts;

use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;

interface RouteValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function matches(Route $route, AbstractRequest $request);
}
