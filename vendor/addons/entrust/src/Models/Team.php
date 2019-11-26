<?php

namespace Addons\Entrust\Models;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Addons\Entrust\Traits\TeamTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Addons\Entrust\Contracts\TeamInterface;

class Team extends Model implements TeamInterface
{
    use TeamTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('entrust.tables.teams');
    }
}
