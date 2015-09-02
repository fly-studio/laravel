<?php
namespace Addons\Core;

use Illuminate\Support\ServiceProvider as SP;
//use Illuminate\Translation\Translator;
//use Illuminate\Contracts\Validation\Validator;
//use Translator,Validator;
use Addons\Core\Http\ResponseFactory;
class ServiceProvider extends SP
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
		//$this->app->alias('core', 'Addons\\Core\\Core');
		$this->app->singleton('core', function($app)
		{
			return new Core();
		});

		$this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
			return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['redirect']);
		});

		$this->mergeConfigFrom(__DIR__ . '/../config/attachment.php', 'attachment');
		$this->mergeConfigFrom(__DIR__ . '/../config/mimes.php', 'mimes');
		$this->mergeConfigFrom(__DIR__ . '/../config/validation.php', 'validation');
		
	}
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([__DIR__ . '/../config/attachment.php' => config_path('attachment.php')], 'config');
		$this->publishes([__DIR__ . '/../config/mimes.php' => config_path('mimes.php')], 'config');
		$this->publishes([__DIR__ . '/../config/validation.php' => config_path('validation.php')], 'config');

		$this->app['view']->addLocation(realpath(__DIR__.'/../resources/views/'));
		$this->app['translator']->addNamespace('core', realpath(__DIR__.'/../resources/lang/'));

		$this->app['view']->share('url', [
			'current' => app('Illuminate\Routing\UrlGenerator')->current(),
			'previous' => app('Illuminate\Routing\UrlGenerator')->previous(),
		]);

		$this->app['validator']->resolver( function( $translator, $data, $rules, $messages = [], $customAttributes = []) {
			return new Validation\Validator( $translator, $data, $rules, $messages, $customAttributes );
		});

		$this->app['router']->group(['namespace' => 'Addons\\Core\\Controllers'], function($router) {
			require __DIR__.'/routes.php';
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Addons\\Core\\Core'];
	}
}