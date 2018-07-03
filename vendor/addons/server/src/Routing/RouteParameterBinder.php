<?php

namespace Addons\Server\Routing;

use Illuminate\Support\Arr;
use Addons\Server\Routing\Route;
use Addons\Server\Contracts\AbstractRequest;

class RouteParameterBinder {

	/**
     * The route instance.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Create a new Route parameter binder instance.
     *
     * @param  Route  $route
     * @return void
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Get the parameters for the route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function parameters(AbstractRequest $request)
    {
        // If the route has a regular expression for the host part of the URI, we will
        // compile that and get the parameter matches for this domain. We will then
        // merge them into this parameters array so that this array is completed.
        $parameters = $this->bindParameters($request);

        return $this->replaceDefaults($parameters);
    }

    /**
     * Get the parameter matches for the path portion of the URI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function bindParameters(AbstractRequest $request)
    {
        if (!$this->route->compiled)
            return [];

        $keywords = $request->keywords();

        preg_match($this->route->compiled->getRegex(), $keywords, $matches);

        return $this->matchToKeys(array_slice($matches, 1));
    }

    /**
     * Combine a set of parameter matches with the route's keys.
     * KEY为字符串的即有效
     *
     * @param  array  $matches
     * @return array
     */
    protected function matchToKeys(array $matches)
    {
        return collect($matches)->reject(function ($value, $key) {
            return is_numeric($key);
        })->all();
    }

    /**
     * Replace null parameters with their defaults.
     *
     * @param  array  $parameters
     * @return array
     */
    protected function replaceDefaults(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? Arr::get($this->route->defaults, $key);
        }

        foreach ($this->route->defaults as $key => $value) {
            if (! isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }


}
