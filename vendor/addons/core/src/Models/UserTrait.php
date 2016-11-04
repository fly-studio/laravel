<?php
namespace Addons\Core\Models;
use DB;
trait UserTrait{

	public static function bootUserTrait()
	{
		static $userBooted;
		if (!$userBooted)
		{
			//自动创建extra等数据
			static::created(function($user){
				$user->finance()->create([]);
			});
			$userBooted= true;
		}
		
	}
}