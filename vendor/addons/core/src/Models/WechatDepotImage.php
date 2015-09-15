<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotImage extends Model{
	public $auto_cache = true;
	protected $guarded = [];

	public function depot()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepot', 'wdid', 'id');
	}
}