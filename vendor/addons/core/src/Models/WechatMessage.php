<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMessage extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('Addons\\Core\\Models\\WechatAccount', 'id', 'aid');
	}

	

}