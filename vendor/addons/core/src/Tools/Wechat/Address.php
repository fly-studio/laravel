<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Cache,Session;
use Addons\Core\Models\WechatUser;
use Illuminate\Http\Exception\HttpResponseException;

class Address {

	private $api;
	private $wechatUser;

	public function __construct($options, $waid = NULL, WechatUser $wechatUser = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
		$this->setWechatUser($wechatUser);
	}

	public function setWechatUser(WechatUser $wechatUser)
	{
		$this->wechatUser = $wechatUser;
		return $this;
	}

	public function authenticate()
	{	
		$access_token = $this->getAccessToken();
		if (!empty($access_token)) return $access_token;

		$url = app('url')->full();
		$json = $this->api->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->api->getOauthRedirect($url,"jsapi_addr","snsapi_base");
			throw new HttpResponseException(redirect($oauth_url));//\Illuminate\Http\RedirectResponse
			return false;
		} else
			$this->setAccessToken($json['access_token'], $json['expires_in']);

		return $json['access_token'];
	}

	public function getAccessToken()
	{
		return Cache::get('wechat-oauth2-access_token-'.$this->wechatUser->getKey(), NULL);
	}

	private function setAccessToken($access_token, $expires)
	{
		Cache::put('wechat-oauth2-access_token-'.$this->wechatUser->getKey(), $access_token, $expires / 60);
	}

	public function getAPI()
	{
		return $this->api;
	}

	/**
	 * 设置jsapi_address参数
	 */
	public function getConfig($url = NULL)
	{

		$timeStamp = time();
		$nonceStr = $this->api->generateNonceStr();
		empty($url) && $url = app('url')->full();
		return [
			'appId' => $this->api->appid,
			'scope' => 'jsapi_address',
			'signType' => 'sha1',
			'addrSign' => $this->getAddrSign($url,$timeStamp,$nonceStr,$this->getAccessToken()),
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
			'appid' => $this->api->appid,
			'noncestr' => $nonceStr,
			'timestamp' => $timeStamp,
			'url' => $url,
		);
		return $this->api->getSignature($arrdata);
	}

}
