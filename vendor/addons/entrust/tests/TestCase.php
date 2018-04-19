<?php

namespace Addons\Entrust\Tests;

use Orchestra\Testbench\TestCase as Base;

class TestCase extends Base
{
    protected function getPackageProviders($app)
    {
        return [\Addons\Entrust\ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['Addons\Entrust' => 'Addons\Entrust\Facade'];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('entrust.user_models.users', 'Addons\Entrust\Tests\Models\User');
        $app['config']->set('entrust.models', [
            'role' => 'Addons\Entrust\Tests\Models\Role',
            'permission' => 'Addons\Entrust\Tests\Models\Permission',
            'team' => 'Addons\Entrust\Tests\Models\Team',
        ]);
    }

    public function migrate()
    {
        $migrations = [
            \Addons\Entrust\Tests\Migrations\UsersMigration::class,
            \Addons\Entrust\Tests\Migrations\SetupTables::class,
        ];

        foreach ($migrations as $migration) {
            (new $migration)->up();
        }
    }
}
