<?php

namespace Addons\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */

use Addons\Core\Models\Tree;
use Addons\Entrust\Traits\RoleTrait;
use Illuminate\Support\Facades\Config;
use Addons\Entrust\Contracts\RoleInterface;

class Role extends Tree implements RoleInterface
{
    use RoleTrait;

    public $orderKey = NULL;
    public $pathKey = NULL;
    public $levelKey = NULL;

    public $fire_caches = ['roles'];
    protected $touches = ['perms'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    public $guarded = ['id'];


    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('entrust.roles_table');
    }

    public function getRoles()
    {
        $roles = [];
        $_roles = Cache::remember('roles', config('cache.ttl'), function() {return $this->with('perms')->orderBy('id', 'ASC')->get();});
        foreach ($_roles->toArray() as $role) {
            $role['prems'] = array_map(function($v) { return $v['name']; }, $role['perms']);
            $roles[$role['name']] = $role;
        }
        return $roles;
    }

}
