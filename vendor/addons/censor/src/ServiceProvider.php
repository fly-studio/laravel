<?php
namespace Addons\Censor;

use Event;
use Addons\Censor\Factory;
use Addons\Censor\Ruling\Ruler;
use Addons\Censor\File\FileLoader;
use Addons\Censor\Validation\ValidatorEx;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
		$this->app->instance('path.censors', $this->censorsPath());

		$this->app->singleton('ruler.loader', function ($app) {
			return new FileLoader($app['files'], $app['path.censors']);
		});

		$this->app->singleton('ruler', function ($app) {
			$loader = $app['ruler.loader'];

			// When registering the translator component, we'll need to set the default
			// locale as well as the fallback locale. So, we'll grab the application
			// configuration so we can easily get both of these values from there.
			$locale = $app['config']['app.locale'];

			$ruler = new Ruler($loader, $locale);

			$ruler->setFallback($app['config']['app.fallback_locale']);

			return $ruler;
		});

		$this->app->singleton('censor', function ($app) {
			return new Factory($app['ruler']);
		});

		$this->app->alias('ruler', Ruler::class);
		$this->app->alias('censor', Factory::class);

	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['validator']->resolver( function( $translator, $data, $rules, $messages = [], $customAttributes = []) {
			return new ValidatorEx( $translator, $data, $rules, $messages, $customAttributes );
		});

		Event::listen(LocaleUpdated::class, function(LocaleUpdated $locale){
			$this->app['ruler']->setLocale($locale->locale);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['addons.censor'];
	}

	/**
	 * Get the path to the language files.
	 *
	 * @return string
	 */
	public function censorsPath()
	{
		return $this->app->resourcePath().DIRECTORY_SEPARATOR.'censors';
	}

}
