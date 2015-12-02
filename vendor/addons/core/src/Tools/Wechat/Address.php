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
		!empty($wechatUser) && $this->setWechatUser($wechatUser);
	}

	public function setWechatUser(WechatUser $wechatUser)
	{
		$this->wechatUser = $wechatUser;
		return $this;
	}

	public function authenticate()
	{	
		$result = $this->getAccessToken();
		if (!empty($result)) return TRUE;

		$url = app('url')->full();
		$json = $this->api->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->api->getOauthRedirect($url,"jsapi_address","snsapi_base");
			throw new HttpResponseException(redirect($oauth_url));//\Illuminate\Http\RedirectResponse
			return false;
		} else
			$this->setAccessToken([$json['access_token'], $_GET['code'], $_GET['state']], $json['expires_in']);

		return TRUE;
	}

	public function getAccessToken()
	{
		return Cache::get('wechat-oauth2-access_token-'.$this->wechatUser->getKey(), NULL);
	}

	private function setAccessToken($data, $expires)
	{
		Cache::put('wechat-oauth2-access_token-'.$this->wechatUser->getKey(), $data, $expires / 60);
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
		list($access_token, $code, $state) = $this->getAccessToken();
		empty($url) && $url = app('url')->full();
		$url .= (strpos($url, '?') !== false ? '&' : '?') . 'code='.$code.'&state='.$state;
		return [
			'appId' => $this->api->appid,
			'scope' => 'jsapi_address',
			'signType' => 'sha1',
			'addrSign' => $this->getAddrSign($url,$timeStamp,$nonceStr,$access_token),
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
			'timestamp' => strval($timeStamp),
			'url' => $url,
		);
		return $this->api->getSignature($arrdata);
	}

}
