<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\WechatMessageMediaTrait;
class WechatMessageMedia extends Model{
	use WechatMessageMediaTrait;

	public $auto_cache = true;
	protected $guarded = [];
	public $incrementing = false;

	public function message()
	{
		return $this->hasOne(get_namespace($this).'\\WechatMessage', 'id', 'id');
	}

}