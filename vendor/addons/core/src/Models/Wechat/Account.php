<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\API;
use Addons\Core\Models\Wechat\User as  WechatUserModel;
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