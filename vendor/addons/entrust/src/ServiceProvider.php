<?php

namespace Addons\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Illuminate\Support\ServiceProvider as Base;
use Illuminate\Database\Eloquent\Relations\Relation;

class ServiceProvider extends Base
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'Migration' => 'command.entrust.migration',
        'MakeRole' => 'command.entrust.role',
        'MakePermission' => 'command.entrust.permission',
        'MakeTeam' => 'command.entrust.team',
        'Setup' => 'command.entrust.setup',
        'SetupTeams' => 'command.entrust.setup-teams',
        'MakeSeeder' => 'command.entrust.seeder',
    ];

    /**
     * The middlewares to be registered.
     *
     * @var array
     */
    protected $middlewares = [
        'role' => \Addons\Entrust\Middleware\Role::class,
        'permission' => \Addons\Entrust\Middleware\Permission::class,
        'ability' => \Addons\Entrust\Middleware\Ability::class,
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/entrust.php', 'entrust');

        $this->publishes([
            __DIR__.'/../config/entrust.php' => config_path('entrust.php'),
            __DIR__. '/../config/entrust_seeder.php' => config_path('entrust_seeder.php'),
        ], 'entrust');

        $this->useMorphMapForRelationships();

        $this->registerMiddlewares();

        if (class_exists('\Blade')) {
            $this->registerBladeDirectives();
        }
    }

    /**
     * If the user wants to use the morphMap it uses the morphMap.
     *
     * @return void
     */
    protected function useMorphMapForRelationships()
    {
        if ($this->app['config']->get('entrust.use_morph_map')) {
            Relation::morphMap($this->app['config']->get('entrust.user_models'));
        }
    }

    /**
     * Register the middlewares automatically.
     *
     * @return void
     */
    protected function registerMiddlewares()
    {
        if (!$this->app['config']->get('entrust.middleware.register')) {
            return;
        }

        $router = $this->app['router'];

        if (method_exists($router, 'middleware')) {
            $registerMethod = 'middleware';
        } elseif (method_exists($router, 'aliasMiddleware')) {
            $registerMethod = 'aliasMiddleware';
        } else {
            return;
        }

        foreach ($this->middlewares as $key => $class) {
            $router->$registerMethod($key, $class);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEntrust();

        $this->registerCommands();
    }

    /**
     * Register the blade directives.
     *
     * @return void
     */
    private function registerBladeDirectives()
    {
        (new RegistersBladeDirectives)->handle($this->app->version());
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerEntrust()
    {
        $this->app->bind('entrust', function ($app) {
            return new Entrust($app);
        });

        $this->app->alias('entrust', 'Addons\Entrust\Entrust');
    }

    /**
     * Register the given commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        foreach (array_keys($this->commands) as $command) {
            $method = "register{$command}Command";

            call_user_func_array([$this, $method], []);
        }

        $this->commands(array_values($this->commands));
    }

    protected function registerMigrationCommand()
    {
        $this->app->singleton('command.entrust.migration', function () {
            return new \Addons\Entrust\Commands\MigrationCommand();
        });
    }

    protected function registerMakeRoleCommand()
    {
        $this->app->singleton('command.entrust.role', function ($app) {
            return new \Addons\Entrust\Commands\MakeRoleCommand($app['files']);
        });
    }

    protected function registerMakePermissionCommand()
    {
        $this->app->singleton('command.entrust.permission', function ($app) {
            return new \Addons\Entrust\Commands\MakePermissionCommand($app['files']);
        });
    }

    protected function registerMakeTeamCommand()
    {
        $this->app->singleton('command.entrust.team', function ($app) {
            return new \Addons\Entrust\Commands\MakeTeamCommand($app['files']);
        });
    }

    protected function registerSetupCommand()
    {
        $this->app->singleton('command.entrust.setup', function () {
            return new \Addons\Entrust\Commands\SetupCommand();
        });
    }

    protected function registerSetupTeamsCommand()
    {
        $this->app->singleton('command.entrust.setup-teams', function () {
            return new \Addons\Entrust\Commands\SetupTeamsCommand();
        });
    }

    protected function registerMakeSeederCommand()
    {
        $this->app->singleton('command.entrust.seeder', function () {
            return new \Addons\Entrust\Commands\MakeSeederCommand();
        });
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
