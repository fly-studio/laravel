<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\Wechat;
class Js {
	private $wechat;

	public function __construct($options, $waid = NULL)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options, $waid);
	}

	public function getWechat()
	{
		return $this->wechat;
	}

	public function getConfig($url = NULL)
	{
		empty($url) && $url = app('url')->current();
		return $this->wechat->getJsSign($url, NULL, NULL, $this->wechat->appid);
	}
}