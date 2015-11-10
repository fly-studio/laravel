<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\User as  WechatUserTool;
use Addons\Core\Models\WechatUser;
use Illuminate\Http\Exception\HttpResponseException;
use Session;
class OAuth2 {
	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function authenticate($url = NULL, $scope = 'snsapi_base', $bindUser = false)
	{	
		$wechatUser = $this->getUser();
		if (!empty($wechatUser)) return $wechatUser;

		empty($url) && $url = app('url')->full();
		$json = $this->api->getOauthAccessToken();
		if (empty($json))
		{
			!empty($_GET['code']) && dd(app('url')->full(), $this->api->errCode, $this->getUser());
			$scope == 'hybrid' && $scope = 'snsapi_base'; //混杂模式下，第一次访问静默授权
			$oauth_url = $this->api->getOauthRedirect($url, $scope, $scope);
			throw new HttpResponseException(redirect($oauth_url));//\Illuminate\Http\RedirectResponse
		}
		else
		{
			$wechatUserTool = new WechatUserTool($this->api);
			$wechatUser = $wechatUserTool->updateWechatUser($json['openid'], $json['access_token'], $scope != 'hybrid');
			if ($scope == 'hybrid' && $_GET['state'] == 'snsapi_base' && empty($wechatUser['nickname']) ) //混杂模式下，静默授权没有取到用户的资料（也就是未关注），重新访问普通授权页面
			{
				$oauth_url =$this->api->getOauthRedirect($url, 'snsapi_userinfo', 'snsapi_userinfo');
				throw new HttpResponseException(redirect($oauth_url));//\Illuminate\Http\RedirectResponse
			}
			$this->setUser($wechatUser);

			if ($bindUser)
				$user = $wechatUserTool->bindToUser($wechatUser);
		}

		return $this->getUser();
	}

	public function getAPI()
	{
		return $this->api;
	}

	public function getUser()
	{
		$wuid = Session::get('wechat-oauth2-'.$this->api->appid.'-user', NULL);
		return empty($wuid) ? false : WechatUser::find($wuid);
	}

	private function setUser(WechatUser $wechatUser)
	{
		Session::put('wechat-oauth2-'.$this->api->appid.'-user', $wechatUser->getKey());
		Session::save();
	}
}