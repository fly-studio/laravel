<?php
namespace Addons\Core;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
//use Illuminate\Translation\Translator;
//use Illuminate\Contracts\Validation\Validator;
//use Translator,Validator;
use Addons\Core\Http\ResponseFactory;
use Addons\Core\Http\UrlGenerator;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Str;
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

		/*$this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
			return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['redirect']);
		});*/
		//replace class
		$this->app->bind('Illuminate\Contracts\Routing\ResponseFactory', ResponseFactory::class);
		$this->app->bind('Illuminate\Contracts\Routing\UrlGenerator', UrlGenerator::class);

		$this->mergeConfigFrom(__DIR__ . '/../config/mimes.php', 'mimes');
		$this->mergeConfigFrom(__DIR__ . '/../config/socketlog.php', 'socketlog');

		$this->registerPlugins();
	}

	private function registerPlugins()
	{
		//自动加载plugins下的ServiceProvider	
		$loader = require SYSPATH.'/vendor/autoload.php';
		foreach (Finder::create()->directories()->in(PLUGINSPATH.'vendor')->depth(0) as $path)
		{
			$name = basename($path);
			$namespace = 'Plugins\\'.Str::studly($name).'\\';
			$loader->setPsr4($namespace.'App\\', array($path.DIRECTORY_SEPARATOR.'app'));
			$loader->setPsr4($namespace, array($path));
			$config = $path.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'plugins.php';
			file_exists($config) && config()->set('plugins.'.$name, require($config) );
			config()->set('plugins.'.$name.'.path', $path );
			config()->set('smarty.template_path', (array)config('smarty.template_path', []) + [$name => $path.DIRECTORY_SEPARATOR.'resources/views']);

			//如果你不满意bootPlugins中的配置，还可以在对应的ServiceProvider中自定义
			$provider = $namespace.'ServiceProvider';
			file_exists($path.DIRECTORY_SEPARATOR.'ServiceProvider.php') && $this->app->register(new $provider($this->app));
		}

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
		$this->publishes([__DIR__ . '/../config/socketlog.php' => config_path('socketlog.php')], 'config');

		$this->app['view']->addLocation(realpath(__DIR__.'/../resources/views/'));
		$this->app['translator']->addNamespace('core', realpath(__DIR__.'/../resources/lang/'));

		$this->app['view']->share('url', [
			'current' => app('Illuminate\Routing\UrlGenerator')->current(),
			'full' => app('Illuminate\Routing\UrlGenerator')->full(),
			'previous' => app('Illuminate\Routing\UrlGenerator')->previous(),
		]);

		$this->app['validator']->resolver( function( $translator, $data, $rules, $messages = [], $customAttributes = []) {
			return new Validation\Validator( $translator, $data, $rules, $messages, $customAttributes );
		});

		$this->bootPlugins();

	}

	private function bootPlugins()
	{
		foreach(config('plugins') as $name => $config)
		{
			if (!isset($config['register'])) continue;

			$namespace = 'Plugins\\'.Str::studly($name);
			$config['register']['validation'] && $this->mergeConfigFrom($config['path'] . '/config/validation.php', 'validation');
			$config['register']['view'] && $this->app['view']->addNamespace($name, realpath($config['path'].'/resources/views/'));
			$config['register']['translator'] && $this->app['translator']->addNamespace('plugins.'.$name, realpath($config['path'].'/resources/lang/'));
			$config['register']['router'] && $this->app['router']->group(['namespace' => $namespace.'\\App\\Http\\Controllers'], function($router) use ($config) {
				require $config['path'].'/routes.php';
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