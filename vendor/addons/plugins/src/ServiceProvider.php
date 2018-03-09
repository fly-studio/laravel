<?php

namespace Addons\Plugins;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Addons\Plugins\Events\EventDispatcher;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/plugins.php', 'plugins');
		$this->registerPlugins();
	}

	private function registerPlugins()
	{
		//自动加载plugins下的配置，和ServiceProvider
		$loader = require __DIR__.'/../../../autoload.php';
		$router = $this->app['router'];
		//Read Config
		$original_config = require(__DIR__.'/../config/templates/plugin.php');
		$plugins = config('plugins');

		foreach (Finder::create()->directories()->in($plugins['paths'])->depth(0) as $path)
		{
			$path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

			$file = $path.'config'.DIRECTORY_SEPARATOR.'plugin.php';
			//read config
			$config = array_merge($original_config, file_exists($file) ? require($file) : []);

			$name = !empty($config['name']) ? $config['name'] : basename(rtrim($path, DIRECTORY_SEPARATOR));

			if (isset($plugins['plugins'][$name]))
				$config = array_merge($config, $plugins['plugins'][$name]);

			//set path name namespace
			$config['path'] = $path;
			$config['name'] = $name;
			$config['namespace'] = $namespace = !empty($config['namespace']) ? $config['namespace'] : 'Plugins\\'.Str::studly($name);

			//set config
			config()->set('plugins.plugins.'.$name, $config);
			if (!$config['enabled']) continue;

			//set psr-4 and include the files
			$loader->setPsr4($namespace.'\\App\\', array($path.'app'));
			$loader->setPsr4($namespace.'\\', array($path));
			foreach($config['files'] as $file)
			{
				$file = $path.$file;
				if (empty($GLOBALS['__composer_autoload_files'][$file])) {
					require $file;

					$GLOBALS['__composer_autoload_files'][$file] = true;
				}
			}

			//read configs
			foreach ($config['configs'] as $file)
				$this->mergeConfigFrom($config['path'].'config/'.$file.'.php', $file);

			//register middleware
			foreach ($config['routeMiddleware'] as $key => $middleware)
				$router->aliasMiddleware($key, $middleware);
			foreach ($config['middlewareGroups'] as $group => $middlewares)
				foreach($middlewares as $middleware)
					$router->pushMiddlewareToGroup($group, $middleware);

			//这里提供更加灵活的plugins/ServiceProvider.php的配置方式，注意$config['register']中配置所对应的程序会优先于plugins/ServiceProvider.php
			$provider = $namespace.'\ServiceProvider';
			file_exists($path.'ServiceProvider.php') && $this->app->register(new $provider($this->app));
		}
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([__DIR__ . '/../config/plugins.php' => config_path('plugins.php')], 'config');

		$this->bootPlugins();
	}

	private function bootPlugins()
	{
		$loader = require __DIR__.'/../../../autoload.php';
		$router = $this->app['router'];
		$censor = $this->app->has('censor') ? $this->app['censor'] : null;
		$plugins = config('plugins.plugins');

		foreach($plugins as $name => $config)
		{
			if (!$config['enabled']) continue;
			// 发布config文件
			foreach ($config['configs'] as $file)
				$this->publishes([$config['path'].'config/'.$file.'.php' => config_path($file.'.php')], 'config');
			//add smarty's path
			config()->set('smarty.template_path', (array)config('smarty.template_path', []) + [$name => $config['path'].'resources/views']);
			//加载
			!empty($config['register']['view']) && $this->loadViewsFrom(realpath($config['path'].'resources/views/'), $name);
			!is_null($censor) && !empty($config['register']['censor']) && $censor->addNamespace($name, realpath($config['path'].'resources/censors/'));
			!empty($config['register']['translator']) && $this->loadTranslationsFrom(realpath($config['path'].'resources/lang/'), $name);
			if (!empty($config['register']['migrate']) && $this->app->runningInConsole())
				$this->loadMigrationsFrom(realpath($config['path'].'database/migrations'));
			if (!empty($config['register']['router']) && !$this->app->routesAreCached())
				foreach($config['routes'] as $key => $route)
				{
					$router->prefix($route['prefix'])
					 ->middleware(array_merge([$key], $route['middleware']))
					 ->namespace(empty($route['namespace']) ? $config['namespace'].'\App\Http\Controllers' : $route['namespace'])
					 ->group($config['path'].'routes/'.$key.'.php');
				}
			if ($this->app->runningInConsole())
			{
				!empty($config['commands']) && $this->commands($config['commands']);
				if (!empty($config['register']['console']))
					require $config['path'].'routes/console.php';
			}
			if (!empty($config['register']['event']))
				app(EventDispatcher::class)->group(['namespace' => $config['namespace'].'\App'], function($eventer) use($config) {
					require $config['path'].'routes/event.php';
				});
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['core'];
	}
}
