<?php
namespace Addons\Core\Controllers;

use Addons\Core\Controllers\Controller;
use Addons\Core\Models\Role;
use Addons\Core\Models\WechatAccount;
use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\OAuth2;
use Illuminate\Database\Eloquent\Model;
class WechatOAuth2Controller extends Controller {

	public $wechat_oauth2_account = NULL;
	public $wechat_oauth2_type = 'snsapi_base'; // snsapi_base  snsapi_userinfo  hybrid
	public $wechat_oauth2_bindUserRole = Role::WECHATER; // 将微信用户绑定到系统用户的用戶組，為空則不綁定

	protected $wechatUser = NULL;

	public function callAction($method, $parameters)
	{
		if (!empty($this->wechat_oauth2_account))
		{
			$account = WechatAccount::findOrFail($this->wechat_oauth2_account);
			$oauth2 = new OAuth2($account->toArray(), $account->getKey());

			$this->wechatUser = $oauth2->getUser();
			if (empty($this->wechatUser))
			{
				//ajax 请求则报错
				if (app('request')->ajax()) 
					return $this->failure('wechat.failure_ajax_oauth2');

				$this->wechatUser = $oauth2->authenticate(NULL, $this->wechat_oauth2_type, $this->wechat_oauth2_bindUserRole);
			}
			$userModel = config('auth.model');
			$this->wechat_oauth2_bindUserRole && $this->user = (new $userModel)->find($this->wechatUser->uid);
		}

		return parent::callAction($method, $parameters);
	}

	public function getWechatUser()
	{
		return $this->wechatUser;
	}

}