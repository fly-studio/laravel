<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\User as  WechatUserTool;
use Addons\Core\Models\WechatUser;
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
			$oauth_url =$this->api->getOauthRedirect($url, 'wxbase', $scope);
			//abort(302, '', ['Location' => $oauth_url]);
			return redirect($oauth_url);
		}
		else
		{
			$wechatUserTool = new WechatUserTool($this->api);
			$wechatUser = $wechatUserTool->updateWechatUser($json['openid'], $json['access_token']);
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

	protected function setUser(WechatUser $wechatUser)
	{
		Session::put('wechat-oauth2-'.$this->api->appid.'-user', $wechatUser->getKey());
		Session::save();
	}
}