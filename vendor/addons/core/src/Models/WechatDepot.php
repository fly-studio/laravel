<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepot extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(dirname(get_class($this)).'\\WechatAccount', 'id', 'waid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function news()
	{
		return $this->belongsToMany(dirname(get_class($this)).'\\WechatDepotNews', 'wechat_depot_news_relation', 'wdnid', 'wdid');
	}

	public function text()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotText', 'id', 'id');
	}

	public function image()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotImage', 'id', 'id');
	}

	public function video()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotVideo', 'id', 'id');
	}

	public function audio()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotVoice', 'id', 'id');
	}

	public function music()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotMusic', 'id', 'id');
	}

	public function callback()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepotCallback', 'id', 'id');
	}

}