<?php
namespace Addons\Core\Models;

trait WechatDepotTrait{

	public static function bootWechatMessageMediaTrait()
	{
		static::deleting(function($depot){
			$depot->callback()->delete();
			$depot->image()->delete();
			$depot->link()->delete();
			$depot->music()->delete();
			$depot->video()->delete();
			$depot->voice()->delete();
			$depot->text()->delete();
			$depot->location()->delete();
			//$depot->news->delete(); //删除对应文章
			$depot->news()->detach(); //删除关联
		});
	}
}