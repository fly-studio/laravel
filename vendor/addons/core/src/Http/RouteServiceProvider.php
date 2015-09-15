<?php
namespace Addons\Core\Http;

use Illuminate\Support\Str;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	protected function setAdminRoutes($route_name, $controller_name = NULL)
	{
		$list = !is_array($route_name) ? [$route_name => $controller_name] : $route_name;
		foreach($list as $route_name => $controller_name)
		{
			$this->app['Illuminate\Routing\Router']->resources([$route_name => $controller_name]);

			//admin/ctrl/data,print,export/json
			$this->app['Illuminate\Routing\Router']->any($route_name.'/{action}/{of}/{jsonp?}', function($action, $of, $jsonp = NULL) use($route_name){
				app('request')->offsetSet('of', $of);
				app('request')->offsetSet('jsonp', $jsonp);
				return $this->callbackUndefinedRoute($route_name, $action);
			})->where('action', '(data|print|export)');
		}
			
	}

	protected function setUndefinedRoutes()
	{
		$this->app['Illuminate\Routing\Router']->any('{ctrl?}/{action?}', function($ctrl = 'home', $action = 'index') {
			return $this->callbackUndefinedRoute( $ctrl, $action);
		});
	}

	private function callbackUndefinedRoute( $ctrl, $action)
	{
		$ctrls = explode('/', $ctrl);
		$ctrls = array_map(function($v){
			return Str::studly($v);
		}, $ctrls);
		$route = $this->app['Illuminate\Routing\Router']->getCurrentRoute();
		$namespace = $route->getAction()['namespace'];
		$className = $namespace.'\\'.implode('\\', $ctrls).'Controller';
		!class_exists($className) && $className = 'Addons\\Core\\Controllers\\'.implode('\\', $ctrls).'Controller';
		(!class_exists($className) || !method_exists($className, $action)) && abort(404);

		$class = new \ReflectionClass($className);
		$function = $class->getMethod($action); //ReflectionMethod
		$route_parameters = $route->resolveMethodDependencies(
			$route->parametersWithoutNulls(), $function
		);
		$parameters = $function->getParameters(); //ReflectionParameter 
		
		$_data = array();
		$count = count($parameters);
		for ($i=0; $i < $count; $i++)
		{ 
			$key = $parameters[$i]->getName();
			if ( array_key_exists($key, $route_parameters) )
			{
				$_data[] = $route_parameters[$key];
			}
			else if ($parameters[$i]->getClass()) //just in $route_parameters;
			{
				$_data[] = $this->app[$parameters[$i]->getClass()->name];
			} else { //from $_GET/$_POST
				$default = $parameters[$i]->isDefaultValueAvailable() ? $parameters[$i]->getDefaultValue() : NULL;
				$_data[] = array_key_exists($key, $_GET) ? Request::input($key) : $default;
			}
		}
		$obj = new $className;
		// Execute the action itself
		return $obj->callAction($action, $_data);
	}
}