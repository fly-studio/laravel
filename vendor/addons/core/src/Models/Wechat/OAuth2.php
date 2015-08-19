<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\Wechat;
use Addons\Core\Models\Wechat\User;
use Session;
class OAuth2 {
	private $wechat;

	public function __construct($options)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options);
	}

	public function authenticate($url = NULL, $scope = 'snsapi_base', $updateUser = false)
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

			if ($updateUser)
				(new User($this->wechat))->updateUser($json['openid'], $json['access_token']);
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