<?php
namespace Addons\Entrust;

use Addons\Core\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Addons\Entrust\Role;
use Addons\Entrust\Traits\UserTrait;
use Addons\Core\Models\CacheTrait;
use Addons\Core\Models\CallTrait;

class User extends Authenticatable
{
	use CacheTrait, CallTrait;
	use Notifiable;
	use SoftDeletes;
	use UserTrait;
	/**
	 * Cache enabled
	 *
	 * @var boolean
	 */
	public $auto_cache = true;

	//不能批量赋值
	protected $guarded = ['id'];
	protected $hidden = ['password', 'remember_token', 'deleted_at'];
	
	protected $dates = ['lastlogin_at'];

	public function get($username)
	{
		return static::findByUsername($username);
	}

	public function add($data, $role_name)
	{
		$data['password'] = bcrypt($data['password']);
		$user = $this->create($data);
		//加入view组
		$user->attachRole(Role::findByName($role_name));
		return $user;
	}

	public function auto_password($username)
	{
		$username = strtolower($username);
		return md5($username.config('app.key').md5($username));
	}

}

