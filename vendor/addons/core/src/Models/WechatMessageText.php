<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMessageText extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function message()
	{
		return $this->hasOne(get_namespace($this).'\\WechatMessage', 'id', 'id');
	}

	
}