<?php

namespace Addons\Server\Routing;

use Illuminate\Container\Container;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Routing\Route\DependencyResolverTrait;

class ControllerDispatcher
{
    use DependencyResolverTrait;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(array $parameters, $controller, $method)
    {
        $parameters = $this->resolveClassMethodDependencies(
            $parameters,
            $controller,
            $method
        );

        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }

        return $controller->{$method}(...array_values($parameters));
    }

    /**
     * Get the middleware for the controller instance.
     *
     * @param  \Illuminate\Routing\Controller  $controller
     * @param  string  $method
     * @return array
     */
    public function getMiddleware($controller, $method)
    {
        if (! method_exists($controller, 'getMiddleware')) {
            return [];
        }

        return collect($controller->getMiddleware())->reject(function ($data) use ($method) {
            return static::methodExcludedByOptions($method, $data['options']);
        })->pluck('middleware')->all();
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param  string  $method
     * @param  array  $options
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options)
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
            (! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}
