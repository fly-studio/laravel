<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Models\User as UserModel;
use Addons\Core\Models\Role as RoleModel;
use Addons\Core\Models\Attachment as AttachmentModel;
use Addons\Core\Models\WechatUser;
use Cache;
use Carbon\Carbon;
class User {

	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function getAPI()
	{
		return $this->api;
	}
	/**
	 * 根据OPENID查询用户资料
	 * @param  string  $openid     OPENID
	 * @param  string  $access_token 如果是通过OAuth2授权，则需要传递此参数
	 * @param  boolean $cache        是否缓存该资料
	 * @return array                 返回对应资料
	 */
	public function getUserInfo($openid, $access_token = NULL, $cache = TRUE) {
		if (empty($openid))
			return FALSE;

		$result = array();
		$hashkey = 'wechat-userinfo-' . $openid. '/'.$this->api->appid;

		if (!$cache || is_null($result = Cache::get($hashkey, null))) {
			$result = empty($access_token) ? $this->api->getUserInfo($openid) : $this->api->getOauthUserinfo($access_token, $openid);;
			if (isset($result['nickname'])) { //订阅号 无法获取昵称，则不加入缓存
				$attachment = (new AttachmentModel)->download(0, $result['headimgurl'], 'wechat-avatar-'.$openid, 'jpg');
				$result['avatar_aid'] = $attachment->getKey();
				Cache::put($hashkey, $result, 12 * 60); //0.5 day
			}
		}
		return $result;
	}

	/**
	 * 更新微信资料(如果没有则添加用户资料)
	 * 
	 * @param  string $openid      	OPENID
	 * @param  string $access_token     如果是通过OAuth2授权，则需要传递此参数
	 * @param  string $role_name        组名，只在添加用户时有效
	 * @param  integer $update_expire 	多少分钟更新一次?
	 * @return integer                  返回UID
	 */
	public function updateWechatUser($openid, $access_token = NULL, $update_expired = 1440)
	{
		if (empty($openid))
			return FALSE;

		$hashkey = 'update-wechatuser-'.$openid. '/'.$this->api->appid;
		return Cache::remember($hashkey, $update_expired, function() use ($openid, $access_token){
			$wechatUser = WechatUser::firstOrCreate([
				'openid' => $openid,
				'waid' => $this->api->waid,
			]);
			$wechat = $this->getUserInfo($wechatUser->openid, $access_token);
			if (empty($wechat))
				throw new \Exception("Get wechat'suser failure");
		
			//公众号绑定开放平台,可获取唯一ID
			empty($wechatUser->unionid) && $wechatUser->update(['unionid' => $wechat['unionid'] ?: $wechatUser->openid.'/'.$this->api->appid]);
			if (isset($wechat['nickname']))
			{
				//将所有唯一ID匹配的资料都更新
				$wechatUsers = WechatUser::where('unionid', $wechatUser->unionid)->get();
				foreach($wechatUsers as $v)
					$v->update([
						'nickname' => $wechat['nickname'], 
						'gender' => $wechat['sex'],
						'is_subscribed' => !empty($wechat['subscribe']) , //没有打开开发者模式 无此字段
						'subscribed_at' => !empty($wechat['subscribe_time']) ? Carbon::createFromTimestamp($wechat['subscribe_time']) : NULL,
						'country' => $wechat['country'],
						'province' => $wechat['province'],
						'city' => $wechat['city'],
						'language' => $wechat['language'],
						'remark' => !empty($wechat['remark']) ? $wechat['remark'] : NULL,//没有打开开发者模式 无此字段
						'groupid' => !empty($wechat['groupid']) ? $wechat['groupid'] : NULL,//没有打开开发者模式 无此字段
						'avatar_aid' => $wechat['avatar_aid'],
					]);
				
			}
			return $wechatUser;
		});
	}

	public function bindToUser(WechatUser $wechatUser, $role_name = RoleModel::WECHATER, $update_expired = 1440)
	{		
		$user = !empty($wechatUser->uid) ? UserModel::find($wechatUser->uid) : (new UserModel)->get($wechatUser->unionid);
		empty($user) && $user = (new UserModel)->add([
			'username' => $wechatUser->unionid,
			'password' => (new UserModel)->auto_password($wechatUser->unionid),
		], $role_name);

		$wechatUser->update(['uid' => $user->getKey()]);

		$hashkey = 'update-user-from-wechat-'.$user->getKey();
		Cache::remember($hashkey, $update_expired, function() use ($wechatUser, $user) {
			$user->update([
				'nickname' => $wechatUser->nickname,
				'gender' => $wechatUser->gender,
				'avatar_aid' => $wechatUser->avatar_aid,
			]);
			return time();
		});
		return $user;
	}

}