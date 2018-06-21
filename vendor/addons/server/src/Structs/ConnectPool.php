<?php

namespace Addons\Server\Structs;

use Addons\Server\Structs\ServerOptions;

class ConnectPool implements \ArrayAccess {

	protected $items = [];
	protected $binds = [];

	public function set($fd, ServerOptions $options): ServerOptions
	{
		if (!$this->offsetExists($fd))
		{
			$this->items[$fd] = $options;
			$options->logger('info', 'Add connect to pool: '.dechex($fd));
		}

		return $this->items[$fd];
	}

	public function get($fd): ServerOptions
	{
		return isset($this->items[$fd]) ? $this->items[$fd] : null;
	}

	public function remove($fd)
	{
		if ($this->offsetExists($fd))
			$this->get($fd)->logger('info', 'Remove connect to pool: '.dechex($fd));

		unset($this->items[$fd], $this->binds[$fd]);
	}

	public function clear($fd)
	{
		foreach ($this->items as $key => $value)
			$this->remove($key);
	}

	public function offsetExists($fd): bool
	{
		return array_key_exists($fd, $this->items);
	}

	public function offsetSet($fd, $value): AbstractRequestFactory
	{
		return $this->items[$fd] = $value;
	}

	public function offsetGet($fd): AbstractRequestFactory
	{
		return $this->offsetExists($fd) ? $this->items[$fd] : null;
	}

	public function offsetUnset($fd)
	{
		$this->remove($fd);
	}

	public function getBindIf($fd, string $key, callable $callback)
	{
		$value = $this->binds[$fd][$key] ?? null;
		if (is_null($value))
			$this->bind($fd, $key, $value = call_user_func($callback));
		return $value;
	}

	public function getBind($fd, string $key = null)
	{
		if (is_null($key))
			return $this->binds[$fd] ?? null;

		return $this->binds[$fd][$key] ?? null;
	}

	public function bind($fd, string $key, $value)
	{
		if (!$this->offsetExists($fd))
			return null;

		$this->binds[$fd][$key] = $value;
		return $this;
	}

	public function unbind($fd, string $key = null)
	{
		if (is_null($key))
			unset($this->binds[$fd]);
		else
			unset($this->binds[$fd][$key]);

		return $this;
	}

}
