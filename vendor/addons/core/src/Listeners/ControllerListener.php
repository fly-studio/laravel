<?php

namespace Addons\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Addons\Core\Contracts\Events\ControllerEvent;
use Addons\Core\Contracts\Listeners\ControllerListener as ControllerListenerContract;

abstract class ControllerListener extends ControllerListenerContract implements ShouldQueue
{
	use InteractsWithQueue;
	// auto call class 'App\Listener\ControllerListener@method' without defined
	protected $controllerListeners = [
		// 'App\Http\Controllers\HomeController' => [
		// 		'App\Listener\HomeControllerListener', //auto set Controller's method name
		// 		'App\Listener\HomeControllerListener1@handle',
		// 	],
	];

	public function handle(ControllerEvent $event) 
	{
		$className = $event->getClassName();
		$methodName = $event->getMethod();

		return $this->loadControllerListeners($className, $methodName, func_get_args());
	}

	

}