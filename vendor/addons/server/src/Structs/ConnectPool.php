<?php

namespace Addons\Server\Structs;

use Addons\Server\Structs\ServerOptions;

class ConnectPool implements \ArrayAccess {

	protected $items = [];
	/*private $booted = false;

	final public function boot(): void
	{
		if ($this->booted)
			return;

		if (method_exists($this, 'boot'.static::class)){
			$this->{'boot'.static::class()};
		}

		$this->booted = true;
	}*/

	public function set($fd, ServerOptions $options): ServerOptions
	{
		if (!$this->offsetExists($fd))
		{
			$this->items[$fd] = $options;
			$options->logger('info', 'Create connect to pool: '.dechex($fd));
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

		unset($this->items[$fd]);
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

}
