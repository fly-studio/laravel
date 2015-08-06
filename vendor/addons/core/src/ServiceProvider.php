<?php
namespace Addons\Core;

use Illuminate\Support\ServiceProvider as SP;
//use Illuminate\Translation\Translator;
//use Illuminate\Contracts\Validation\Validator;
//use Translator,Validator;
class ServiceProvider extends SP
{
	/**
     * 指定是否延缓提供者加载。
     *
     * @var bool
     */
    protected $defer = true;
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->alias('core', 'Addons\\Core\\Core');
		$this->app->singleton('core', function($app)
		{
			return new Core();
		});
	}
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['view']->addLocation(realpath(__DIR__.'/../resources/views/'));
		$this->app['translator']->addNamespace('Core', realpath(__DIR__.'/../resources/lang/'));

		$this->app['view']->share('key', 'value');

		$this->app['validator']->resolver( function( $translator, $data, $rules, $messages = [], $customAttributes = []) {
			return new Validation\AnsiExtended( $translator, $data, $rules, $messages, $customAttributes );
		} );
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