<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Models\WechatUser;
use Carbon\Carbon;
class Url {

	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function getAPI()
	{
		return $this->api;
	}
	
	public function getURL($url, WechatUser $user = NULL)
	{
		return queue_url('wechat').'?url='.rawurlencode($url).(!empty($user) ? '&wuid='.rawurlencode($user->getKey()) : '');
	}

}