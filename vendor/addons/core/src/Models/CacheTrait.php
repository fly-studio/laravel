<?php
namespace Addons\Core\Models;
use Illuminate\Support\Str;
use Cache;
trait CacheTrait{

	/**
	 * Cache enabled
	 *
	 * @var boolean
	 */
	public $auto_cache = false;
	/**
	 * Cache live in minutes
	 *
	 * @var integer
	 */
	public $cache_ttl = 15;
	/**
	 * forget these keys when fire
	 * 
	 * @var array
	 */
	public $fire_caches = [];

	/**
	 * Fire the given event for the model.
	 *
	 * @param  string  $event
	 * @param  bool    $halt
	 * @return mixed
	 */
	protected function fireModelEvent($event, $halt = true)
	{
		$result = parent::fireModelEvent($event, $halt);
		if (in_array($event, ['created', 'updated', 'deleted', 'saved',]))
		{
			$primaryKey = $this->primaryKey;
			if ($this->auto_cache === true)
				$this->forgetCache($this->$primaryKey);
			//clear the other fire
			$this->forgetCache($this->fire_caches);
		}
		return $result;
	}

	/**
	 * Call static method.
	 *
	 * Embeded cache with find method.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return Object
	 */
	public static function __callStatic($method, $parameters)
	{
		$instance = new static();
		switch ($method) {
			case 'find':
				if (!isset($parameters[1]))
				{
					$key = $parameters[0];			
					if ($instance->auto_cache === true) {
						if (! $result = $instance->getCache($key)) {
							$result = parent::__callStatic('find', $parameters);
							! is_null($result) && $instance->putCache($key, $result);
						}
					} else
						$result = parent::__callStatic('find', $parameters); 
					return $result;
				}
				break;
			case 'putCache':
			case 'rememberCache':
			case 'getCache':
			case 'forgetCache':
				return call_user_func_array([$instance, $method], $parameters);
				break;
		}
		unset($instance);
		return parent::__callStatic($method, $parameters); 
	}

	/**
	 * Get uniqe key
	 *
	 * @param  integer $key
	 * @param  string  $array
	 */
	public static function cache_key($key)
	{
		return Str::lower(get_called_class()).'_'.$key;
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

}

