<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMenu extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatAccount', 'id', 'waid');
	}

	public function depot()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatDepot', 'id', 'wdid');
	}
}