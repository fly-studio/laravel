<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMessageLink extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function message()
	{
		return $this->hasOne('App\\WechatMessage', 'id', 'id');
	}

	
}