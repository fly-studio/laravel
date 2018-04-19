<?php

use Mockery as m;
use Addons\Entrust\Entrust;
use Addons\Entrust\Tests\TestCase;

class Test extends TestCase
{
    protected $entrust;
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->entrust = m::mock('Addons\Entrust\Entrust[user]', [$this->app]);
        $this->user = m::mock('_mockedUser');
    }

    public function testHasRole()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasRole')->with('UserRole', null, false)->andReturn(true)->once();
        $this->user->shouldReceive('hasRole')->with('NonUserRole', null, false)->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->hasRole('UserRole'));
        $this->assertFalse($this->entrust->hasRole('NonUserRole'));
        $this->assertFalse($this->entrust->hasRole('AnyRole'));
    }

    public function testCan()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasPermission')->with('user_can', null, false)->andReturn(true)->once();
        $this->user->shouldReceive('hasPermission')->with('user_cannot', null, false)->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->can('user_can'));
        $this->assertFalse($this->entrust->can('user_cannot'));
        $this->assertFalse($this->entrust->can('any_permission'));
    }

    public function testAbility()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('ability')->with('admin', 'user_can', null, [])->andReturn(true)->once();
        $this->user->shouldReceive('ability')->with('admin', 'user_cannot', null, [])->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->ability('admin', 'user_can'));
        $this->assertFalse($this->entrust->ability('admin', 'user_cannot'));
        $this->assertFalse($this->entrust->ability('any_role', 'any_permission'));
    }

    public function testUserOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('owns')->with($postModel, null)->andReturn(true)->once();
        $this->user->shouldReceive('owns')->with($postModel, 'UserId')->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->owns($postModel, null));
        $this->assertFalse($this->entrust->owns($postModel, 'UserId'));
        $this->assertFalse($this->entrust->owns($postModel, 'UserId'));
    }

    public function testUserHasRoleAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->once()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasRoleAndOwns')->with('admin', $postModel, [])->andReturn(true)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->hasRoleAndOwns('admin', $postModel));
        $this->assertFalse($this->entrust->hasRoleAndOwns('admin', $postModel));
    }

    public function testUserCanAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->entrust->shouldReceive('user')->andReturn($this->user)->once()->ordered();
        $this->entrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('canAndOwns')->with('update-post', $postModel, [])->andReturn(true)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->entrust->canAndOwns('update-post', $postModel));
        $this->assertFalse($this->entrust->canAndOwns('update-post', $postModel));
    }

    public function testUser()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $this->entrust = new Entrust($this->app);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($this->user)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($this->user, $this->entrust->user());
    }
}
