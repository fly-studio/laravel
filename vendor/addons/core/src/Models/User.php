<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


use Addons\Entrust\Traits\UserTrait as EntrustUserTrait;
use Addons\Core\Models\Role;
use Addons\Core\Models\UserTrait;

use Addons\Core\Models\CacheTrait;
use Addons\Core\Models\CallTrait;

class User extends Authenticatable
{
	use CacheTrait, CallTrait;
	use Notifiable;
	use SoftDeletes, EntrustUserTrait;
	use UserTrait;
	/**
	 * Cache enabled
	 *
	 * @var boolean
	 */
	public $auto_cache = true;


	//能批量赋值
	//protected $fillable = ['username', 'password'];
	//不能批量赋值
	protected $guarded = ['id'];
	protected $dates = ['lastlogin_at'];
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'deleted_at'];

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

	public function _gender()
	{
		return $this->hasOne(get_namespace($this).'\\Catalog', 'id', 'gender');
	}

	public function finance()
	{
		return $this->hasOne(get_namespace($this).'\\UserFinance', 'id', 'id');
	}
}

