<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotNews extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function depot()
	{
		return $this->belongsToMany(dirname(get_class($this)).'\\WechatDepot', 'wechat_depot_news', 'wdid', 'wnid');
	}

}