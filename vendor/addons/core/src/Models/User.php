<?php

namespace Addons\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Auth\Authenticatable;
//use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Addons\Core\Models\Role;

class User extends Model implements AuthenticatableContract/*, CanResetPasswordContract*/
{
	use Authenticatable/*, CanResetPassword*/;
	use SoftDeletes, EntrustUserTrait;
	use CacheTrait;

	//protected $dates = ['deleted_at'];
	//表名
	//protected $table = 'users';
	//主键名称
	//protected $primaryKey = 'id';

	//能批量赋值
	//protected $fillable = ['username', 'password'];
	//不能批量赋值
	protected $guarded = ['id'];
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	public function get($username)
	{
		return $this->where('username', $username)->first();
	}

	public function add($data, $rid = Role::VIEWER)
	{
		$data['password'] = bcrypt($data['password']);
		$user = $this->create($data);
		//加入view组
		$user->attachRole($this->roles[$rid]['id']);
		return $user->id;
	}

	public function auto_password($username)
	{
		$username = strtolower($username);
		return md5($username.env('APP_KEY').md5($username));
	}
}
