<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany('App\\WechatUser', 'waid', 'id');
	}

	public function messages()
	{
		return $this->hasMany('App\\WechatMessage', 'waid', 'id');
	}

	public function menus()
	{
		return $this->hasMany('App\\WechatMenu', 'waid', 'id');
	}

	public function depots()
	{
		return $this->hasMany('App\\WechatDepot', 'waid', 'id');
	}

}