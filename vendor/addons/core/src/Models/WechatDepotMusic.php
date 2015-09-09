<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotMusic extends Model{
	public $auto_cache = true;
	protected $guarded = [];

	public function depot()
	{
		return $this->belongsTo('App\\WechatDepot', 'wdid', 'id');
	}
}