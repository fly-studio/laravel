<?php

namespace Addons\Entrust\Models;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Addons\Core\Models\Tree;
use Addons\Entrust\Traits\RoleTrait;
use Addons\Core\Models\TreeCacheTrait;
use Illuminate\Support\Facades\Config;
use Addons\Entrust\Contracts\RoleInterface;

class Role extends Tree implements RoleInterface
{
    use RoleTrait, TreeCacheTrait;

    public $orderKey = NULL;
    public $pathKey = NULL;
    public $levelKey = NULL;

    protected $touches = ['permissions'];

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
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('entrust.tables.roles');
    }
}
