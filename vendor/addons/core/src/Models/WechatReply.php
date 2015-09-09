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

	public function content()
	{
		return $this->belongsToMany('App\\WechatReplyContent', 'wechat_reply_relation', 'wrcid', 'wrid');
	}
}