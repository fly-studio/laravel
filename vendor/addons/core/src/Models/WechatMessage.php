<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatMessage extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatAccount', 'id', 'waid');
	}

	public function user()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatUser', 'id', 'wuid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function depot()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatDepot', 'id', 'wdid');
	}

	public function link()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageLink', 'id', 'id');
	}

	public function location()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageLocation', 'id', 'id');
	}

	public function video()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageMedia', 'id', 'id');
	}

	public function audio()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageMedia', 'id', 'id');
	}

	public function image()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageMedia', 'id', 'id');
	}

	public function text()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatMessageText', 'id', 'id');
	}


}