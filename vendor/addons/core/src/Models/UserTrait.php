<?php
namespace Addons\Core\Models;
use DB;
trait UserTrait{

	public static function bootUserTrait()
	{
		//自动创建extra等数据
		User::created(function($user){
			/*UserExtra::create([
				'id' => $user->id,
			]);*/
		});
	}
}