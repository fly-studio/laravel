<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepot extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne('Addons\\Core\\Models\\WechatAccount', 'id', 'waid');
	}

	public function relation()
	{
		$method = $this->type;
		return $this->$method();
	}

	public function articles()
	{
		return $this->belongsToMany('Addons\\Core\\Models\\WechatArticle', 'wechat_depot_relation', 'wrid', 'wdid');
	}

	public function text()
	{
		return $this->belongsTo('Addons\\Core\\Models\\WechatText', 'id', 'wdid');
	}

	public function picture()
	{
		return $this->belongsTo('Addons\\Core\\Models\\WechatMedia', 'id', 'wdid');
	}

	public function video()
	{
		return $this->belongsTo('Addons\\Core\\Models\\WechatMedia', 'id', 'wdid');
	}

	public function audio()
	{
		return $this->belongsTo('Addons\\Core\\Models\\WechatMedia', 'id', 'wdid');
	}

}