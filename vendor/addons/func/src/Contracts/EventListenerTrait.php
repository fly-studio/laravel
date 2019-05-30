<?php

namespace Addons\Func\Contracts;


trait EventListenerTrait {

	protected $listeners = [];

	/**
	 * $observer->addListener('onStart', function(){  });
	 * $observer->addListener('onStop', [$obj, 'methodName']);
	 * $observer->addListener('onStop', 'methodName', true);
	 *
	 *
	 * @param string $messageName 监听消息名
	 * @param callable $callback  回调函数
	 * @param bool $overwrite     是否覆盖之前的同名消息，如果是，则会清除之前的监听
	 */
	public function addListener(string $messageName, callable $callback, bool $overwrite = false)
	{
		if ($overwrite) $this->removeListener($messageName);

		$this->listeners[$messageName][] = $callback;
		return $this;
	}

	public function removeListener(string $messageName, callable $callback = null)
	{
		if (is_null($callback))
			unset($this->listener[$messageName]);
		else if (!empty($this->listeners[$messageName]))
			foreach($this->listeners[$messageName] as $key => $_call)
				if ($callback === $_call)
					unset($this->listener[$messageName][$key]);
		return $this;
	}

	public function removeAllListeners()
	{
		$this->listeners = [];
		return $this;
	}

	public function trigger(string $messageName, ...$args)
	{
		if (!empty($this->listeners[$messageName]))
			foreach($this->listeners[$messageName] as $listener)
				call_user_func_array($listener, $args);
		return $this;
	}

	public function getListeners()
	{
		return $this->listeners;
	}

}
