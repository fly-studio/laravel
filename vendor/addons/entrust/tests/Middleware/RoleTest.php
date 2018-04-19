<?php

namespace Addons\Entrust\Tests\Middleware;

use Mockery as m;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Addons\Entrust\Middleware\Role;

class RoleTest extends MiddlewareTest
{
    public function testHandle_IsGuestWithMismatchingRole_ShouldAbort403()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $middleware = new Role($this->guard);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Auth::shouldReceive('guard')->with('web')->andReturn($this->guard);
        $this->guard->shouldReceive('guest')->andReturn(true);
        App::shouldReceive('abort')->with(403)->andReturn(403);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user'));
    }

    public function testHandle_IsLoggedInWithMismatchRole_ShouldAbort403()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Addons\Entrust\Tests\Models\User')->makePartial();
        $middleware = new Role($this->guard);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->guard->shouldReceive('guest')->andReturn(false);
        Auth::shouldReceive('guard')->with(m::anyOf('web', 'api'))->andReturn($this->guard);
        $this->guard->shouldReceive('user')->andReturn($user);
        $user->shouldReceive('hasRole')
            ->with(
                ['admin', 'user'],
                m::anyOf(null, 'TeamA'),
                m::anyOf(true, false)
            )
            ->andReturn(false);
        App::shouldReceive('abort')->with(403)->andReturn(403);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user'));

        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user', 'guard:api'));

        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user', 'require_all'));

        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user', 'require_all|guard:api'));

        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user', 'TeamA', 'require_all'));

        $this->assertEquals(403, $middleware->handle($this->request, function () {
        }, 'admin|user', 'TeamA', 'guard:api|require_all'));
    }

    public function testHandle_IsLoggedInWithMatchingRole_ShouldNotAbort()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Addons\Entrust\Tests\Models\User')->makePartial();
        $middleware = new Role($this->guard);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->guard->shouldReceive('guest')->andReturn(false);
        Auth::shouldReceive('guard')->with(m::anyOf('web', 'api'))->andReturn($this->guard);
        $this->guard->shouldReceive('user')->andReturn($user);
        $user->shouldReceive('hasRole')
            ->with(
                ['admin', 'user'],
                m::anyOf(null, 'TeamA'),
                m::anyOf(true, false)
            )
            ->andReturn(true);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user'));

        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user', 'guard:api'));

        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user', 'require_all'));

        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user', 'require_all|guard:api'));

        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user', 'TeamA', 'require_all'));

        $this->assertNull($middleware->handle($this->request, function () {
        }, 'admin|user', 'TeamA', 'require_all|guard:api'));
    }
}
