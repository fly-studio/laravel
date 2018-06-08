<?php

namespace Addons\Server\Routing;

use Closure;
use Addons\Server\Routing\Route;
use Illuminate\Container\Container;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ServiceCallable;
use Addons\Server\Routing\Router\NewTrait;
use Addons\Server\Routing\Router\RunTrait;
use Addons\Server\Routing\Router\BindTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Contracts\AbstractService;
use Addons\Func\Contracts\AbstractGroupLoader;
use Addons\Server\Routing\Router\MiddlewareTrait;

class Router extends AbstractGroupLoader {

	use BindTrait, MiddlewareTrait, RunTrait, NewTrait;

	protected $items = [];
	protected $patternItems = [];
	protected $closureItems = [];

	/**
     * Create a new Router instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        $this->events = $events;
        $this->container = $container ?: new Container;
        $this->setLoadResolver(function($file_path, $router) {
			require $file_path;
		});
    }

	public function findRoute(string $eigenvalue) : ?Route
	{
		if (isset($this->items[$eigenvalue]))
			return $this->items[$eigenvalue];

		foreach($this->patternItems as $pattern => $val)
			if (preg_match($pattern, $eigenvalue) === 1)
				return $val;

		foreach($this->closureItems as $val)
			if (call_user_func($val['closure'], $eigenvalue) === true)
				return $val['route'];

		return null;
	}

	public function register(string $eigenvalue, $action)
	{
		$this->items[$eigenvalue] = $this->createRoute( $action );
		return $this;
	}

	public function registerClosure(Closure $callback, $action) {

		$this->closureItems[] =  [
			'closure' => $callback,
			'route' => $this->createRoute( $action ),
		];
		return $this;
	}

	public function registerRegex(string $pattern, $action)
	{
		$this->patternItems[$pattern] = $this->createRoute( $action );
		return $this;
	}

}
