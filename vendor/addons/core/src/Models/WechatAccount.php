<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany(dirname(get_class($this)).'\\WechatUser', 'waid', 'id');
	}

	public function messages()
	{
		return $this->hasMany(dirname(get_class($this)).'\\WechatMessage', 'waid', 'id');
	}

	public function menus()
	{
		return $this->hasMany(dirname(get_class($this)).'\\WechatMenu', 'waid', 'id');
	}

	public function depots()
	{
		return $this->hasMany(dirname(get_class($this)).'\\WechatDepot', 'waid', 'id');
	}

}