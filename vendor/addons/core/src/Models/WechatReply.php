<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\WechatMessage;

class WechatReply extends Model{
	public $auto_cache = true;
	protected $guarded = ['id'];

	public $fire_caches = ['wechat-replies'];

	const MATCH_TYPE_WHOLE = 'whole';
	const MATCH_TYPE_PART = 'part';
	const MATCH_TYPE_SUBSCRIBE = 'subscribe';
	const REPLY_TYPE_RANDOM = 'random';
	const REPLY_TYPE_ALL = 'all';

	public function account()
	{
		return $this->hasOne(get_namespace($this).'\\WechatAccount', 'id', 'waid');
	}


	public function depots()
	{
		return $this->belongsToMany(get_namespace($this).'\\WechatDepot', 'wechat_reply_depot', 'wrid', 'wdid');
	}

	public function getDepots()
	{
		return $this->reply_count > 0 ? $this->depots->random($this->reply_count) : $this->depots;
	}

	public function getReplies()
	{
		return $this->rememberCache('wechat-replies', function(){
			$result = [];
			foreach(static::all() as  $v)
				if ($v['match_type'] == static::MATCH_TYPE_SUBSCRIBE)
					$result[ $v['waid'] ] [ $v['match_type'] ] = $v;
				else
					$result[ $v['waid'] ] [ $v['match_type'] ] [ $v['keywords'] ] = $v;
			
			$result;
		});
	}

	/**
	 * 检索关键字回复
	 * 
	 * @param  \Addons\Core\Models\WechatMessage $message
	 * @return Illuminate\Support\Collection [\Addons\Core\Models\WechatDepots, ...]
	 */
	public function autoReply(WechatMessage $message)
	{
		$replies = $this->getReplies();
		$result = null;
		if (isset($replies[$message->waid][static::MATCH_TYPE_WHOLE]) && array_key_exists($message->content, $replies[$message->waid][static::MATCH_TYPE_WHOLE])) {
			$result = $replies[$message->waid][static::MATCH_TYPE_WHOLE][$message->content];
		} elseif (isset($result[$message->waid][static::MATCH_TYPE_PART])) {
			$replace = array_map(function($v) {return '#$@{'.$v->getKey().'}@$#'; }, $result[$message->waid][static::MATCH_TYPE_PART]);
			$content = strtr($message->content, $replace);
			if (strcmp($content, $message->content) != 0) { //有匹配对象
				preg_match('/#\$@\{(\d*)\}@\$#/g', $content, $matches);
				is_numeric($matches[1]) && $result = static::find($matches[1]);
			}
		}
		unset($replies);
		return !empty($result) ? $result->getDepots() : $this->newCollection();
	}

	/**
	 * 关注自动回复
	 * 
	 * @return Illuminate\Support\Collection [\Addons\Core\Models\WechatDepots, ...]
	 */
	public function subscribeReply()
	{
		$replies = $this->getReplies();
		return isset($replies[$message->waid][static::MATCH_TYPE_SUBSCRIBE]) ? $replies[$message->waid][static::MATCH_TYPE_SUBSCRIBE]->getDepots() : false;//$this->newCollection();
	}
}