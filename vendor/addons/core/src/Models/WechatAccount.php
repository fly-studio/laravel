<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany(get_namespace($this).'\\WechatUser', 'waid', 'id');
	}

	public function messages()
	{
		return $this->hasMany(get_namespace($this).'\\WechatMessage', 'waid', 'id');
	}

	public function menus()
	{
		return $this->hasMany(get_namespace($this).'\\WechatMenu', 'waid', 'id');
	}

	public function depots()
	{
		return $this->hasMany(get_namespace($this).'\\WechatDepot', 'waid', 'id');
	}

}