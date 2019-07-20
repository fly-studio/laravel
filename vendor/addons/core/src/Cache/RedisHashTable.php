<?php

namespace Addons\Core\Cache;

use Illuminate\Support\Arr;
use Illuminate\Cache\RedisStore;
use Illuminate\Redis\RedisManager;

class RedisHashTable {

	protected $redis;

	public function __construct(string $connection = null)
	{
		$this->redis = app('redis')->connection($connection);
	}

	public function hmget(string $key, array $fields)
	{
		return array_combine($fields,
			array_map(function($v) {
					return !empty($v) ? unserialize($v) : null;
				},
				$this->redis->hmget($key, $fields)
			)
		);
	}

	public function hmset(string $key, array $data)
	{
		return $this->redis->hmset($key, array_map(function($v){
			return serialize($v);
		}, $data));
	}

	public function __call($method, $parameters)
	{
		return $this->redis->$method(...$parameters);
	}
}
