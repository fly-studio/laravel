<?php
namespace Addons\Core\Models;
use Illuminate\Support\Str;
use Cache;
trait CacheTrait{

	/**
	 * Cache live in minutes
	 *
	 * @var integer
	 */
	protected $cache_ttl = 15;
	/**
	 * forget these keys when fire
	 * 
	 * @var array
	 */
	protected $fire_caches = [];

	/**
	 * Fire the given event for the model.
	 *
	 * @param  string  $event
	 * @param  bool    $halt
	 * @return mixed
	 */
	protected static function bootCacheTrait()
	{
		static::created(function($model) {
			$model->clearCache();
		});
		static::updated(function($model) {
			$model->clearCache();
		});
		static::deleted(function($model) {
			$model->clearCache();
		});
		static::saved(function($model) {
			$model->clearCache();
		});
	}

	/**
	 * Get uniqe key
	 *
	 * @param  integer $key
	 * @param  string  $array
	 */
	private function cache_key($key)
	{
		return $key;//Str::lower(get_called_class()).'_'.$key;
	}

	protected function rememberCache($key, $callback, $expiredMinutes = NULL)
	{
		$key = $this->cache_key($key);
		empty($expiredMinutes) && $expiredMinutes = $this->cache_ttl;
		return Cache::remember($key, $expiredMinutes, $callback);
	}

	protected function putCache($key, $value, $expiredMinutes = NULL)
	{
		$key = $this->cache_key($key);
		empty($expiredMinutes) && $expiredMinutes = $this->cache_ttl;
		return Cache::put($key, $value, $expiredMinutes);
	}

	protected function getCache($key, $default = NULL)
	{
		$key = $this->cache_key($key);
		return Cache::get($key, $default);
	}

	protected function forgetCache($key)
	{
		$keys = empty($key) ? [] : (array)$key + func_get_args();
		foreach ($keys as $key) {
			$key = $this->cache_key($key);
			Cache::forget($key);
		}
		return TRUE;
	}

	protected function clearCache()
	{
		$this->forgetCache($this->fire_caches);
	}

}

