<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Model;

class WechatMessage extends Model{
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('Addons\\Core\\Models\\Wechat\\WechatAccount', 'id', 'aid');
	}

	

}