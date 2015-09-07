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

	public function articles()
	{
		return $this->belongsToMany('App\\WechatArticle', 'wechat_depot_relation', 'wrid', 'wdid');
	}

	public function text()
	{
		return $this->belongsTo('App\\WechatText', 'id', 'wdid');
	}

	public function picture()
	{
		return $this->belongsTo('App\\WechatMedia', 'id', 'wdid');
	}

	public function video()
	{
		return $this->belongsTo('App\\WechatMedia', 'id', 'wdid');
	}

	public function audio()
	{
		return $this->belongsTo('App\\WechatMedia', 'id', 'wdid');
	}

}