<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatDepotVoice extends Model{
	public $auto_cache = true;
	protected $guarded = [];

	public function depot()
	{
		return $this->belongsTo(dirname(get_class($this)).'\\WechatDepot', 'wdid', 'id');
	}
}