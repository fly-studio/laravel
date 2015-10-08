<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\User as WechatUserTool;
use Session;
class Account {


	public function getAccountID()
	{
		return Session::get('wechat-account-id', NULL);
	}

	public function setAccountID($accountid)
	{
		return Session::put('wechat-account-id', $accountid);

	}
}