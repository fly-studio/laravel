<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
class Js {
	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function getWechat()
	{
		return $this->api;
	}

	public function getConfig($url = NULL)
	{
		empty($url) && $url = app('url')->current();
		return $this->api->getJsSign($url, NULL, NULL, $this->api->appid);
	}
}