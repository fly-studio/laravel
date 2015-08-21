<?php
namespace Addons\Core\Models;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Zizaco\Entrust\Contracts\EntrustRoleInterface;
use Zizaco\Entrust\Traits\EntrustRoleTrait;
use Addons\Core\Models\Model;
use Illuminate\Support\Facades\Config;
use Addons\Core\Models\CacheTrait;
class Role extends Model implements EntrustRoleInterface
{
	use EntrustRoleTrait;

	public $auto_cache = true;
	public $fire_caches = ['roles'];

	const ADMIN = 'admin';
	const MANGER = 'manger';
	const OWNER = 'owner';
	const LEADER = 'leader';
	const VIEWER = 'viewer';

	//不能批量赋值
	protected $guarded = [];

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
        $this->table = Config::get('entrust.roles_table');
    }

	public function getRoles()
	{
		$roles = [];
		$_roles = $this->rememberCache('roles', function() {return $this->orderBy('id', 'ASC')->get();});
		foreach ($_roles as $role) {
			$roles[$role['name']] = $role->toArray() + ['perms' =>[]];
			$perms = $role->perms;
			foreach($perms as $perm)
				$roles[($role['name'])]['perms'][] = $perm['name'];
		}
		return $roles;
	}
}
