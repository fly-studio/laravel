<?php
namespace Addons\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder as Elasticsearch;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;


/**
 * Class ServiceProvider
 *
 * @package Cviebrock\LaravelElasticsearch
 */
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
		$configPath = realpath(__DIR__ . '/../config/elasticsearch.php');
		$this->publishes([
			$configPath => config_path('elasticsearch.php'),
		]);

		app(\Laravel\Scout\EngineManager::class)->extend('elasticsearch', function(){
			return new AdvancedElasticsearchEngine(
				Elasticsearch::fromConfig(config('scout.elasticsearch.config')),
				config('scout.elasticsearch.index')
			);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

		$this->mergeConfigFrom(__DIR__ . '/../config/elasticsearch.php', 'elasticsearch');
		config('elasticsearch.connections.default.logObject') === 'monolog' && config(['elasticsearch.connections.default.logObject' => $app->make('log')]);
		config('scout.elasticsearch.config.logger') === 'monolog' && config(['scout.elasticsearch.config.logger' => $app->make('log')]);

		$app->singleton('elasticsearch.factory', function($app) {
			return new Factory();
		});

		$app->singleton('elasticsearch', function($app) {
			return new Manager($app, $app['elasticsearch.factory']);
		});

		$app->singleton(Client::class, function($app) {
			return $app['elasticsearch']->connection();
		});
	}
}
