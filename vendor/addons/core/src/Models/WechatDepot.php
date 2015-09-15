<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepot extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(get_namespace($this).'\\WechatAccount', 'id', 'waid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function news()
	{
		return $this->belongsToMany(get_namespace($this).'\\WechatDepotNews', 'wechat_depot_news_relation', 'wdnid', 'wdid');
	}

	public function text()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotText', 'id', 'id');
	}

	public function image()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotImage', 'id', 'id');
	}

	public function video()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotVideo', 'id', 'id');
	}

	public function audio()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotVoice', 'id', 'id');
	}

	public function music()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotMusic', 'id', 'id');
	}

	public function callback()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepotCallback', 'id', 'id');
	}

}