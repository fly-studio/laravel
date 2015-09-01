<?php
namespace Addons\Core\Models;

trait TreeTrait{

	public static function bootTreeTrait()
	{
		//更新order/level
		static::creating(function($node){
			!empty($node->orderKey) && $node[$node->orderKey] = $node->newOrder();
			!empty($node->levelKey) && $node[$node->levelKey] = $node->getParent()->getLevelKey() + 1;
		});
		//更新path
		static::created(function($node){
			if (!empty($node->pathKey))
			{
				$node[$node->getPathKeyName()] = $node->getParent()->getPathKey() . $node->getKey().'/';
				$node->save();
			}
		});

		//切换父级
		static::updated(function($node) {
			if ($node->isDirty($node->getParentKey()))
			{
				$node->changeParent();
				//放到新Parent的结尾
				$node->moveToLast();
			}
		});
		//删除子集，会自动递归删除后代
		static::deleted(function($node){
			foreach($node->getChildren() as $v)
				$v->delete();
		});
	}
}