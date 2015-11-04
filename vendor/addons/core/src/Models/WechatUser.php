<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatUser extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(get_namespace($this).'\\WechatAccount', 'id', 'waid');
	}

	public function _gender()
	{
		return $this->hasOne(get_namespace($this).'\\Field', 'id', 'gender');
	}

}