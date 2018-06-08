<?php

namespace Addons\Server;

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
				\Addons\Server\Example\Console\TagCommand::class,
				\Addons\Server\Example\Console\RawCommand::class,
				\Addons\Server\Example\Console\GRPCCommand::class,
			]);
		}

	}

	public function provides()
	{
		return [];
	}
}
