<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Models\WechatQrcode;
use Addons\Core\Jobs\WechatQrcode as WechatQrcodeJob;
use Exception;
class Qrcode {
	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function getAPI()
	{
		return $this->api;
	}

	public function getSceneId($id, $expire_seconds = 0)
	{
		$qr = WechatQrcode::firstOrCreate([
			'waid' => $this->api->waid,
			'scene_id' => $id,
			'type' => empty($expire_seconds) ? 'QR_LIMIT_SCENE' : 'QR_SCENE',
		]);
		//没有或者已超时
		if (empty($qr->ticket) || (!empty($qr->expire_seconds) && $qr->created_at->getTimestamp() + $qr->expire_seconds < time()))
			$this->save($qr, $id, empty($expire_seconds))->update(['expire_seconds' => $expire_seconds]);
		return $qr;
	}

	public function getSceneStr($str)
	{
		$qr = WechatQrcode::firstOrCreate([
			'waid' => $this->api->waid,
			'scene_str' => $str,
			'type' => 'QR_LIMIT_STR_SCENE',
		]);

		empty($qr->ticket) && $this->save($qr, $str, 2);
		return $qr;
	}

	private function save(WechatQrcode $qr, $str, $type)
	{
		$result = $this->api->getQRCode($str, $type);
		if ($result !== false)
		{
			$qr->update([
				'ticket' => $result['ticket'],
				'url' => $result['url'],
			]);
			//异步下载
			$job = (new WechatQrcodeJob($qr->getKey()))->onQueue('wechat');
			app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
		}
		return $qr;
	}
}