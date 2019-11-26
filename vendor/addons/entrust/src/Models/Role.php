<?php

namespace Addons\Entrust\Models;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Addons\Core\Models\TreeTrait;
use Addons\Core\Models\BuilderTrait;
use Addons\Entrust\Traits\RoleTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Addons\Entrust\Contracts\RoleInterface;

class Role extends Model implements RoleInterface
{
    use RoleTrait, TreeTrait, BuilderTrait;

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

    public function getOrderKeyName()
    {
        return null;
    }

    public function getPathKeyName()
    {
        return null;
    }

    public function getLevelKeyName()
    {
        return null;
    }
}
