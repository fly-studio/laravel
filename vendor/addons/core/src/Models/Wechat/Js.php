<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\Wechat;
class OAuth2 {
	private $wechat;

	public function __construct($options)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options);
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