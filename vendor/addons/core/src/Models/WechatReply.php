<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatReply extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('App\\WechatAccount', 'id', 'waid');
	}

	public function contents()
	{
		return $this->hasMany('App\\WechatReplyContent', 'wrid', 'id');
	}

	public function autoReply()
	{
		
	}
}