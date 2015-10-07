<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotVoice extends Model{
	public $auto_cache = true;
	protected $guarded = [];
	public $incrementing = false;

	public function depot()
	{
		return $this->belongsTo(get_namespace($this).'\\WechatDepot', 'wdid', 'id');
	}
}