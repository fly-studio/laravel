<?php
namespace Addons\Core;

//namespace Illuminate\Translation\FileLoader;
use Illuminate\Support\ServiceProvider as SP;
use Illuminate\Translation\Translator;

class ServiceProvider extends SP
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

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