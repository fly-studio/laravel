<?php

namespace Addons\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Addons\Core\Contracts\Events\ControllerEvent;
use Addons\Core\Contracts\Listeners\ControllerListener as ControllerListenerContract;

abstract class BeforeControllerListener extends ControllerListenerContract implements ShouldQueue
{
	use InteractsWithQueue;
	// auto call class 'Namespace\App\Listener\ControllerBeforeListener' without defined
	protected $controllerListeners = [
		// 'App\Http\Controllers\HomeController' => [
		// 		'App\Listener\HomeControllerBeforeListener', //auto set Controller's method name
		// 		'App\Listener\HomeControllerBeforeListener1@handle',
		// 	],
	];

	public function handle(ControllerEvent $event) 
	{
		$className = $event->getClassName();
		$methodName = $event->getMethod();

		return $this->loadControllerListeners($className, $methodName, func_get_args(), 'BeforeListener');
	}

}