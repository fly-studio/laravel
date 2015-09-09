<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepot extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('App\\WechatAccount', 'id', 'waid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function news()
	{
		return $this->belongsToMany('App\\WechatDepotNews', 'wechat_depot_news_relation', 'wdnid', 'wdid');
	}

	public function text()
	{
		return $this->belongsTo('App\\WechatDepotText', 'id', 'id');
	}

	public function image()
	{
		return $this->belongsTo('App\\WechatDepotImage', 'id', 'id');
	}

	public function video()
	{
		return $this->belongsTo('App\\WechatDepotVideo', 'id', 'id');
	}

	public function audio()
	{
		return $this->belongsTo('App\\WechatDepotVoice', 'id', 'id');
	}

	public function music()
	{
		return $this->belongsTo('App\\WechatDepotMusic', 'id', 'id');
	}

}