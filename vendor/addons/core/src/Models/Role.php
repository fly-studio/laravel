<?php
namespace Addons\Core\Models;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */

use Addons\Entrust\Contracts\RoleInterface;
use Addons\Entrust\Traits\RoleTrait as EntrustRoleTrait;
use Addons\Core\Models\Model;
use Illuminate\Support\Facades\Config;
use Addons\Core\Models\CacheTrait;
class Role extends Tree implements RoleInterface
{
	use EntrustRoleTrait;

	public $orderKey = NULL;
	public $pathKey = NULL;
	public $levelKey = NULL;

	public $auto_cache = true;
	public $fire_caches = ['roles'];

	//不能批量赋值
	public $guarded = ['id'];

	 /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

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
		$_roles = Cache::remember('roles', 24 * 60, function() {return $this->with('perms')->orderBy('id', 'ASC')->get();});
		foreach ($_roles->toArray() as $role) {
			$role['prems'] = array_map(function($v) { return $v['name'];}, $role['perms']);
			$roles[$role['name']] = $role;
		}
		return $roles;
	}
}
