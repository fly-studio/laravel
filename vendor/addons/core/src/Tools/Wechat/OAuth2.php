<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\User as  WechatUserTool;
use Session;
class OAuth2 {
	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function authenticate($url = NULL, $scope = 'snsapi_base', $bindUser = false)
	{	
		$openid = $this->getOpenID();
		if (!empty($openid)) return true;

		empty($url) && $url = app('url')->current();
		$json = $this->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->api->getOauthRedirect($url, 'wxbase', $scope);
			redirect($oauth_url);
			return false;
		}
		else
		{
			$this->setOpenID($json['openid']);
			$wechatUserTool = new WechatUserTool($this->api);
			$this->wechatUser = $wechatUserTool->updateWechatUser($json['openid'], $json['access_token']);

			if ($bindUser)
				$user = $wechatUserTool->bindToUser($this->wechatUser);
		}

		return true;
	}

	public function getWechat()
	{
		return $this->api;
	}

	protected function getOpenID()
	{
		return Session::get('wechat-oauth2-openid', NULL);
	}

	protected function setOpenID($openid)
	{
		return Session::put('wechat-oauth2-openid', $openid);

	}
}