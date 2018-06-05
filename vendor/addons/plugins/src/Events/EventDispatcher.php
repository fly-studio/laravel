<?php

namespace Addons\Plugins\Events;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Addons\Func\Contracts\AbstractGroupLoader;

class EventDispatcher extends AbstractGroupLoader {

	public function __construct()
	{
		$this->setLoadResolver(function($file_path, $eventer) {
			require realpath($file_path);
		});
	}

	public function execute($prefix, $class, $listener)
	{
		$attributes = $this->mergeWithLastGroup(compact('class', 'listener', 'prefix'));

		extract($attributes);

		$class = $this->prependGroupNamespace($class);

		Event::listen($prefix.$class, $listener);
	}

	public function listen($events, $listener)
	{
		Event::listen($events, $listener);
	}

	public function model($model, $type, $listener)
	{
		$this->execute("eloquent.{$type}: ", $model, $listener);
	}

	public function models($models, $listener)
	{
		foreach ($models as $action => $listener)
		{
			list($model, $type) = explode('@', $action, 2) + ['', '*'];
			$this->model($model, $type, $listener);
		}
	}

	public function controller($controller, $listener, $type = 'after')
	{
		$this->execute("controller.{$type}: ", $controller, $listener);
	}

	public function controllers($controllers, $type = 'after')
	{
		foreach ($controllers as $controller => $listener)
			$this->controller($controller, $listener, $type);
	}

}
