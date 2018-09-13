<?php

namespace Addons\Server\Structs;

use Addons\Server\Structs\ServerOptions;

class ConnectBinder implements \ArrayAccess {

	public $items = [];

	public function __construct(ServerOptions $options)
	{
		$this->items['options'] = $options;
	}

	public function options()
	{
		return $this->items['options'];
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

	public function getBindIf(string $key, callable $callback)
	{
		$value = $this->offsetExists($key) ? $this->items[$key] : null;

		if (is_null($value))
			$this->bind($key, $value = call_user_func($callback));

		return $value;
	}

	public function getBind(string $key)
	{
		return $this->items[$key] ?? null;
	}

	public function bind(string $key, $value)
	{
		$this->items[$key] = $value;
		return $this;
	}

	public function unbind(string $key)
	{
		unset($this->items[$fd][$key]);
		return $this;
	}

}
