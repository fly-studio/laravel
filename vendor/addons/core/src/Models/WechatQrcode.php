<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;

class WechatQrcode extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public function account()
	{
		return $this->hasOne(get_namespace($this).'\\WechatAccount', 'id', 'waid');
	}

	public function depot()
	{
		return $this->hasOne(get_namespace($this).'\\WechatDepot', 'id', 'wdid');
	}

	public function subscribe_depot()
	{
		return $this->hasOne(get_namespace($this).'\\WechatDepot', 'id', 'subscribe_wdid');
	}

	/**
	 * 扫描二维码关注自动回复
	 * 
	 * @return Illuminate\Support\Collection [\Addons\Core\Models\WechatDepots, ...]
	 */
	public function subscribeReply($scene_id, $ticket)
	{
		$qr = $this->where('ticket', '=', $ticket)->orderBy('updated_at','DESC')->first();
		return empty($qr) ? false : $qr->subscribe_depot()->get(); //返回数据集
	}

	/**
	 * 扫描二维码自动回复
	 * 
	 * @return Illuminate\Support\Collection [\Addons\Core\Models\WechatDepots, ...]
	 */
	public function reply($scene_id, $ticket)
	{
		$qr = $this->where('ticket','=',$ticket)->orderBy('updated_at','DESC')->first();
		return empty($qr) ? false : $qr->depot()->get(); //返回数据集
 	}
}