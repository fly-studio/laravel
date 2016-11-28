<?php
namespace Addons\Ucloud;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Addons\Ucloud\Factory;
class ServiceProvider extends BaseServiceProvider
{
	/**
	 * 指定是否延缓提供者加载。
	 *
	 * @var bool
	 */
	protected $defer = false;
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		
		$this->mergeConfigFrom(__DIR__ . '/../config/ucloud.php', 'ucloud');

        $this->app->alias('ucloud', Factory::class);
		$this->app->singleton('ucloud', function ($app) {
            return new Factory($app);
        });
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([__DIR__ . '/../config/ucloud.php' => config_path('ucloud.php')], 'config');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['ucloud'];
	}
}