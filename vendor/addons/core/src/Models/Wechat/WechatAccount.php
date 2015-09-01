<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Model;

class WechatAccount extends Model{
	protected $guarded = ['id'];

	public function users()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatUser', 'waid', 'id');
	}

	public function messages()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatMessage', 'waid', 'id');
	}

	public function articles()
	{
		return $this->hasMany('Addons\\Core\\Models\\Wechat\\WechatArticle', 'waid', 'id');
	}

}