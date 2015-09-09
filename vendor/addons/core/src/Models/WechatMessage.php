<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMessage extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('App\\WechatAccount', 'id', 'waid');
	}

	public function user()
	{
		return $this->hasOne('App\\WechatUser', 'id', 'wuid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function depot()
	{
		return $this->hasOne('App\\WechatDepot', 'id', 'wdid');
	}

	public function link()
	{
		return $this->hasOne('App\\WechatMessageLink', 'id', 'id');
	}

	public function location()
	{
		return $this->hasOne('App\\WechatMessageLocation', 'id', 'id');
	}

	public function video()
	{
		return $this->hasOne('App\\WechatMessageMedia', 'id', 'id');
	}

	public function audio()
	{
		return $this->hasOne('App\\WechatMessageMedia', 'id', 'id');
	}

	public function image()
	{
		return $this->hasOne('App\\WechatMessageMedia', 'id', 'id');
	}

	public function text()
	{
		return $this->hasOne('App\\WechatMessageText', 'id', 'id');
	}


}