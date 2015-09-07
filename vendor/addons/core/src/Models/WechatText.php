<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatText extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function depot()
	{
		return $this->belongsTo('App\\WechatDepot', 'wdid', 'id');
	}
}