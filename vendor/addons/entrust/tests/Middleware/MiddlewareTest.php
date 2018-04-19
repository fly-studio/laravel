<?php

namespace Addons\Entrust\Tests\Middleware;

use Mockery as m;
use Addons\Entrust\Tests\TestCase;

abstract class MiddlewareTest extends TestCase
{
    protected $request;
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->request = m::mock('Illuminate\Http\Request');
        $this->guard = m::mock('Illuminate\Contracts\Auth\Guard');
    }
}
