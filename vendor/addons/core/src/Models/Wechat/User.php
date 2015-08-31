<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Attachment;
use Addons\Core\Models\User as UserModel;
use Addons\Core\Models\Role as RoleModel;
use Addons\Core\Models\Wechat\Wechat;
use Cache;
class User {

	private $wechat, $user;

	public function __construct($options)
	{
		$this->wechat = $options instanceof Wechat ? $options new Wechat($options);
		$this->user = new UserModel();
	}

	public function getWechat()
	{
		return $this->wechat;
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
		$hashkey = 'wechat-userinfo-' . $openid;
		if (!$cache || is_null($result = Cache::get($hashkey, null))) {
			$result = empty($access_token) ? $this->wechat->getUserInfo($openid) : $this->wechat->getOauthUserinfo($access_token, $openid);;
			if (isset($result['nickname'])) { //订阅号 无法获取昵称，则不加入缓存
				$attachment =(new Attachment)->download(0, $result['headimgurl'], 'wechat-avatar-'.$openid, 'jpg');
				$result['avatar_aid'] = $attachment['aid'];
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
	 * @param  integer $update_expire 	多久更新一次?
	 * @return integer                  返回UID
	 */
	public function updateUser($openid, $access_token = NULL, $role_name = NULL, $update_expired = 86400)
	{
		if (empty($openid))
			return FALSE;

		$user = $this->user->get($openid);
		$uid = empty($user) ? $this->user->add([
			'username' => $openid,
			'password' => $this->user->auto_password($openid),
			'nickname' => '',
		], $role_name ?: RoleModel::WECHATER)->id : $user['id'];

		$hashkey = 'update-wechat-'.$uid;
		$last = Cache::get($hashkey, NULL);
		if (is_null($last) || time() - $last > $update_expired) //过了超时时间
		{
			$wechat = $this->getUserInfo($openid, $access_token);
			if (isset($wechat['nickname']))
			{
				$this->user->find($uid)->update([
					'nickname' => $wechat['nickname'], 
					'gender' => $wechat['sex'],
					'avatar_aid' => $wechat['avatar_aid'],
				]);
				Cache::put($hashkey, time(), 60 * 24); // 1 day
			}
		}
		return $uid;
	}

}