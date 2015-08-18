<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\Wechat;
use Cache;
class Address {

	private $wechat;

	public function __construct($options)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options);
	}

	public function authenticate()
	{	
		$access_token = $this->getAccessToken();
		if (!empty($access_token)) return true;

		$url = app('url')->current();
		$json = $this->wechat->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->wechat->getOauthRedirect($url,"jsapi_addr","snsapi_base");
			redirect($oauth_url);
			return false;
		} else
			$this->setAccessToken($json['access_token']);

		return true;
	}

	public function getAccessToken()
	{
		$hashkey = 'wechat-address-token-'. $this->wechat->appid;

		$access_token = Cache::get($hashkey, NULL);
		return $access_token;
	}

	public function setAccessToken($access_token)
	{
		$hashkey = 'wechat-address-token-'. $this->wechat->appid;
		Cache::put($hashkey, $access_token, 60);
	}

	public function getWechat()
	{
		return $this->wechat;
	}

	/**
	 * 设置jsapi_address参数
	 */
	public function getConfig($url = NULL)
	{
		if (!$this->authenticate()) return false;

		$timeStamp = time();
		$nonceStr = $this->generateNonceStr();
		//$this->parameters = json_encode($AddrParameters);
		empty($url) && $url = app('url')->current();
		return [
			'appId' => $this->wechat->appid;
			'scope' => 'jsapi_address',
			'signType' => 'sha1',
			'addrSign' => $this->getAddrSign($url,$timeStamp,$nonceStr,$this->access_token),
			'timeStamp' => $timeStamp,
			'nonceStr' => $nonceStr,
		];
	}

	/**
	 * 获取收货地址JS的签名
	 */
	public function getAddrSign($url, $timeStamp, $nonceStr, $accesstoken = ''){
		$arrdata = array(
			'accesstoken' => $accesstoken,
			'appid' => $this->wechat->appid,
			'noncestr' => $nonceStr,
			'timestamp' => $timeStamp,
			'url' => $url,
		);
		return $this->getSignature($arrdata);
	}

}
