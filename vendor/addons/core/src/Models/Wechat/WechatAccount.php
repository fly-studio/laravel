<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatUser', 'aid', 'id');
	}

	public function messages()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatMessage', 'aid', 'id');
	}

	public function articles()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatArticle', 'aid', 'id');
	}

}