<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany('Addons\\Core\\Models\\WechatUser', 'waid', 'id');
	}

	public function messages()
	{
		return $this->hasMany('Addons\\Core\\Models\\WechatMessage', 'waid', 'id');
	}

	public function depots()
	{
		return $this->hasMany('Addons\\Core\\Models\\WechatDepot', 'waid', 'id');
	}

}