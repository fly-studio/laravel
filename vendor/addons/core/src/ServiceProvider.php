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
	protected $defer = false;
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
		$this->app['translator']->addNamespace('core', realpath(__DIR__.'/../resources/lang/'));
		/*$this->app['response']->header('P3P','CP="CAO PSA OUR"');//解决跨域访问别个页面时丢失session的隐私声明
		if (in_array($this->app['request']->method(), array( 'POST', 'PUT', 'DELETE' )))
		{
			//header no cache when post
			$this->app['response']->header([
				'Expires' => '0',
				'Cache-Control' => 'no-store,private, post-check=0, pre-check=0, max-age=0',
				'Pragma' => 'no-cache',
			]);
		}*/

		$this->app['view']->share('url', [
			'current' => app('Illuminate\Routing\UrlGenerator')->current(),
			'previous' => app('Illuminate\Routing\UrlGenerator')->previous(),
		]);

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