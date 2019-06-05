<?php

namespace Addons\Server;

use Addons\Core\File\Mimes;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

	protected $defer = false;

	public function register()
	{

	}

	public function boot()
	{
		if ($this->app->runningInConsole())
		{
			$this->commands([
				\Addons\Server\Console\NativeHttpCommand::class,
				\Addons\Server\Example\Console\TagCommand::class,
				\Addons\Server\Example\Console\RawCommand::class,
				\Addons\Server\Example\Console\GrpcCommand::class,
			]);

			$this->registerRouter();
		}
	}

	private function registerRouter()
	{
		if ($this->app->routesAreCached()) {
			return;
		}

		$router = $this->app['router'];

		$router->get('static/{path}', function($path) {

		if (file_exists($p = static_path('common/'.$path)))
				return response()->preview($p, [], ['mime_type' => Mimes::getInstance()->mime_by_ext(pathinfo($p, PATHINFO_EXTENSION))]);

			abort(404);

		})->where('path', '.*');

		$router->get('plugins/{path}', function($path) {

			if (file_exists($p = plugins_path($path)))
				return response()->preview($p, [], ['mime_type' => Mimes::getInstance()->mime_by_ext(pathinfo($p, PATHINFO_EXTENSION))]);

			abort(404);

		})->where('path', '.*');
	}

	public function provides()
	{
		return [];
	}
}
