<?php
namespace Addons\Smarty;

use Illuminate\Support\ServiceProvider as SP;

class ServiceProvider extends SP
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../../config/config.php' => config_path('smarty.php')
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'smarty');

		$this->app['view']->addExtension($this->app['config']->get('latrell-smarty.extension', 'tpl'), 'smarty', function ()
		{
			return new SmartyEngine($this->app['config']);
		});
	}
}
