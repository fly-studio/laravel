<?php

namespace Addons\Entrust\Test;

use Addons\Entrust\Tests\TestCase;
use Addons\Entrust\Tests\Models\Permission;

class PermissionTest extends TestCase
{
    protected $permission;

    public function setUp()
    {
        parent::setUp();

        $this->migrate();
        $this->permission = new Permission();
    }

    public function testUsersRelationship()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\MorphToMany', $this->permission->users());
    }

    public function testAccessUsersRelationshipAsAttribute()
    {
        $this->assertEmpty($this->permission->users);
    }

    public function testRolesRelationship()
    {
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Relations\BelongsToMany', $this->permission->roles());
    }
}
