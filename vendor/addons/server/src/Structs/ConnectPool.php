<?php

namespace Addons\Server\Structs;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ConnectBinder;

class ConnectPool implements \ArrayAccess {

	protected $items = [];

	public function set($fd, ServerOptions $options): ConnectBinder
	{
		if (!$this->offsetExists($fd))
		{
			$this->items[$fd] = new ConnectBinder($options);

			$options->logger('info', 'Add connect to pool: '.dechex($fd));
		}

		return $this->items[$fd];
	}

	public function get($fd): ?ConnectBinder
	{
		return isset($this->items[$fd]) ? $this->items[$fd] : null;
	}

	public function remove($fd)
	{
		if ($this->offsetExists($fd))
			$this->get($fd)->options()->logger('info', 'Remove connect to pool: '.dechex($fd));

		unset($this->items[$fd]);
	}

	public function clear()
	{
		foreach ($this->items as $key => $value)
			$this->remove($key);
	}

	public function offsetExists($fd): bool
	{
		return array_key_exists($fd, $this->items);
	}

	public function offsetSet($fd, $value)
	{
		return $this->items[$fd] = $value;
	}

	public function offsetGet($fd)
	{
		return $this->offsetExists($fd) ? $this->items[$fd] : null;
	}

	public function offsetUnset($fd)
	{
		$this->remove($fd);
	}
}
