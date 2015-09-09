<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatReplyContent extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function reply()
	{
		return $this->hasOne('App\\WechatReply', 'id', 'waid');
	}

}