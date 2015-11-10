<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Cache,Session;
use Addons\Core\Models\WechatUser;
use Illuminate\Http\Exception\HttpResponseException;

class Address {

	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function authenticate(WechatUser $wechatUser)
	{	
		$access_token = $this->getAccessToken($wechatUser);
		if (!empty($access_token)) return $access_token;

		$url = app('url')->full();
		$json = $this->api->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->api->getOauthRedirect($url,"jsapi_addr","snsapi_base");
			throw new HttpResponseException(redirect($oauth_url));//\Illuminate\Http\RedirectResponse
			return false;
		} else
			$this->setAccessToken($wechatUser, $json['access_token'], $json['expires_in']);

		return $json['access_token'];
	}

	public function getAccessToken(WechatUser $wechatUser)
	{
		return Cache::get('wechat-oauth2-access_token-'.$wechatUser->getKey(), NULL);
	}

	private function setAccessToken(WechatUser $wechatUser, $access_token, $expires)
	{
		Cache::put('wechat-oauth2-access_token-'.$wechatUser->getKey(), $access_token, $expires / 60);
	}

	public function getAPI()
	{
		return $this->api;
	}

	/**
	 * 设置jsapi_address参数
	 */
	public function getConfig(WechatUser $wechatUser, $url = NULL)
	{

		$timeStamp = time();
		$nonceStr = $this->api->generateNonceStr();
		empty($url) && $url = app('url')->full();
		return [
			'appId' => $this->api->appid,
			'scope' => 'jsapi_address',
			'signType' => 'sha1',
			'addrSign' => $this->getAddrSign($url,$timeStamp,$nonceStr,$this->getAccessToken($wechatUser)),
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
