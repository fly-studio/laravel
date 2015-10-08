<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\WechatMessage;

class WechatTemplate extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

}