<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotNews extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function depots()
	{
		return $this->belongsToMany(get_namespace($this).'\\WechatDepot', 'wechat_depot_news_relation', 'wnid', 'wdid');
	}

}