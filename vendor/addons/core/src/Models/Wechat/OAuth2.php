<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\Wechat;
use Addons\Core\Models\Wechat\User as  WechatUserModel;
use Session;
class OAuth2 {
	private $wechat;

	public function __construct($options, $waid = NULL)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options, $waid);
	}

	public function authenticate($url = NULL, $scope = 'snsapi_base', $bindUser = false)
	{	
		$openid = $this->getOpenID();
		if (!empty($openid)) return true;

		empty($url) && $url = app('url')->current();
		$json = $this->getOauthAccessToken();
		if (empty($json))
		{
			$oauth_url =$this->wechat->getOauthRedirect($url, 'wxbase', $scope);
			redirect($oauth_url);
			return false;
		}
		else
		{
			$this->setOpenID($json['openid']);
			$wechatUserModel = new WechatUserModel($this->wechat);
			$this->wechatUser = $wechatUserModel->updateWechatUser($json['openid'], $json['access_token']);

			if ($bindUser)
				$user = $wechatUserModel->bindToUser($this->wechatUser);
		}

		return true;
	}

	public function getWechat()
	{
		return $this->wechat;
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