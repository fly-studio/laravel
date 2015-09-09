<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMenu extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('App\\WechatAccount', 'id', 'waid');
	}

	public function depot()
	{
		return $this->hasOne('App\\WechatDepot', 'id', 'wdid');
	}
}