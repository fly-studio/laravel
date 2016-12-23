<?php

namespace Addons\Core\Contracts\Events;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\View\View;

class ControllerEvent
{
    use InteractsWithSockets;
    use SerializesAndRestoresModelIdentifiers;

    private $controllerObject;
    private $methodName;
    private $request;
    private $response;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($controllerObject, $methodName, $response = null)
    {
        $request = app('request');
        $this->controllerObject = $controllerObject;
        $this->methodName = $methodName;
        $this->request = [
            'query' => $request->query->all(),
            'request' => $request->request->all(),
            'attributes' => $request->attributes->all(),
            'cookies' => $request->cookies->all(),
            'files' => $request->files->all(),
            'server' => $request->server->all(),
            'content' => $request->content,
        ];

        $this->response = null;
        if (is_scalar($response) || $response instanceof View)
            $this->response = [ 
                'content' => strval($response),
                'status' => 200,
                'headers' => [],
            ]; 
        elseif ($response instanceof Response)
            $this->response = [
                'content' => $response->content(),
                'status' => $response->status(),
                'headers' => $response->headers->all(),
            ];
        else
            $this->response = null;
    }

	public function getClass()
    {
        return $this->controllerObject;
    }

    public function getClassName()
    {
        return get_class($this->controllerObject);
    }

    public function getMethod()
    {
        return $this->methodName;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getRequest()
    {
        static $request;
        if (empty($request))
        {
            $request = Request::createFromBase(new SymfonyRequest($this->request['query'], $this->request['request'], $this->request['attributes'], $this->request['cookies'], $this->request['files'], $this->request['server'], $this->request['content']));

            $router = app(\Illuminate\Routing\Router::class);
            $routes = $router->getRoutes();
            $route = $routes->match($request);

            $request->setRouteResolver(function () use ($route) {
                return $route;
            });
        }
        return $request;
    }

    public function getRoute($param = null)
    {
        $request = $this->getRequest();
        return $request->route($param);
    }

    public function getResponse()
    {
        return !empty($this->response) ? Response::create($this->response['content'], $this->response['status'], $this->response['headers']) : null;
    }
}