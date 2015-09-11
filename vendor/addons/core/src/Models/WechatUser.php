<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatUser extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatAccount', 'id', 'waid');
	}

	public function gender()
	{
		return $this->hasOne(dirname(get_class($this)).'\\Field', 'id', 'gender');
	}

}