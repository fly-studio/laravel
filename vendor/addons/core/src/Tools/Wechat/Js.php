<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\Pay\JsApiPay;
use Addons\Core\Tools\Wechat\API;
use Exception;
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

	/**
	 * 
	 * 获取jsapi支付的参数
	 * @param array $UnifiedOrderResult 统一支付接口返回的数据
	 * @throws Exception
	 * 
	 * @return json数据，可直接填入js函数作为参数
	 */
	public function getPayParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new Exception("参数错误");
		}
		$jsapi = new JsApiPay();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr($this->api->generateNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign($this->api->mchkey));
		return $jsapi->GetValues();
	}
}