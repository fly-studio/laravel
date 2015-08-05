<?php
namespace Addons\Smarty\View;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
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
			__DIR__ . '/../config/smarty.php' => config_path('smarty.php')
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/smarty.php', 'smarty');

		$this->app['view']->addExtension($this->app['config']->get('smarty.extension', 'tpl'), 'smarty', function ()
		{
			return new Engine($this->app['config']);
		});
	}

	 /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['smarty'];
    }
}
