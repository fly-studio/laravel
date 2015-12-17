<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;

use Addons\Core\Models\Attachment;
use Addons\Core\Models\WechatAccount;
use Addons\Core\Models\WechatUser;

use Addons\Core\Jobs\WechatSend;
class Send {

	private $user;

	private $messages;

	public function __construct(WechatUser $user)
	{
		$this->user = $user;
		$this->messages = [];
	}
	/**
	 * [add description]
	 * @param mixed $obj WechatDepot/WechatTemplate/Attachment/String
	 */
	public function add($obj)
	{
		foreach (func_get_args() as $value)
			$this->messages[] = $value;
		return $this;
	}

	public function send($random = NULL)
	{
		$messages = !empty($random) ? array_pick($this->messages, $random) : $this->messages;
		foreach ($messages as $value)
		{
			//Queue
			$job = (new WechatSend($this->user, $value))->onQueue('wechat')/*->delay(1)*/;
			app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
		}
		return true;
	}

}