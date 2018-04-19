<?php

namespace Addons\Entrust\Test;

use Addons\Entrust\Tests\Models\Role;
use Addons\Entrust\Tests\Models\Team;
use Addons\Entrust\Tests\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Addons\Entrust\Tests\TestCase;
use Addons\Entrust\Tests\Models\Permission;

class CacheTest extends TestCase
{
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->migrate();
    }

    public function testUserCanDisableTheRolesAndPermissionsCaching()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $user = User::create(['name' => 'test', 'email' => 'test@test.com']);
        $role = Role::create(['name' => 'role_a'])
            ->attachPermissions([
                Permission::create(['name' => 'permission_a']),
                Permission::create(['name' => 'permission_b']),
                Permission::create(['name' => 'permission_c']),
            ]);

        $user->roles()->attach($role->id);

        $user->permissions()->attach([
            Permission::create(['name' => 'permission_d'])->id => ['team_id' => $team->id ],
            Permission::create(['name' => 'permission_e'])->id => ['team_id' => $team->id],
        ]);

        /*
        |------------------------------------------------------------
        | User Assertion
        |------------------------------------------------------------
        */
        // With cache
        $this->app['config']->set('entrust.use_cache', true);
        $this->assertInternalType('array', $user->cachedRoles());
        $this->assertEquals($user->roles()->get()->toArray(), $user->cachedRoles());

        $this->assertInternalType('array', $user->cachedPermissions());
        $this->assertEquals($user->permissions()->get()->toArray(), $user->cachedPermissions());

        // Without cache
        $this->app['config']->set('entrust.use_cache', false);
        $this->assertInstanceOf('Illuminate\Support\Collection', $user->cachedRoles());
        $this->assertEquals($user->roles()->get(), $user->cachedRoles());

        $this->assertInstanceOf('Illuminate\Support\Collection', $user->cachedPermissions());
        $this->assertEquals($user->permissions()->get(), $user->cachedPermissions());

        /*
        |------------------------------------------------------------
        | Role Assertion
        |------------------------------------------------------------
        */
        // With cache
        $this->app['config']->set('entrust.use_cache', true);
        $this->assertInternalType('array', $role->cachedPermissions());
        $this->assertEquals($role->permissions()->get()->toArray(), $role->cachedPermissions());

        // Without cache
        $this->app['config']->set('entrust.use_cache', false);
        $this->assertInstanceOf('Illuminate\Support\Collection', $role->cachedPermissions());
        $this->assertEquals($role->permissions()->get(), $role->cachedPermissions());
    }
}
