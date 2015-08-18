<?php
namespace Addons\Core\Models;

trait CacheTrait{


	protected function setCache($hashkey, $value, $expiredMinutes = 1440)
	{
		return Cache::put($hashkey, $value, $expiredMinutes);
	}

	protected function getCache($hashkey, $default = NULL)
	{
		return Cache::get($hashkey, $default);
	}

	protected function deleteCache($hashkey)
	{
		$hashkeys = func_get_args();
		foreach ($hashkeys as $hashkey) {
			Cache::forget($hashkey);
		}
		return TRUE;
	}
}