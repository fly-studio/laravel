<?php

namespace Addons\Func\Contracts;


trait EventListenerTrait {

	protected $listeners = [];

	/**
	 * $observer->addListener('onStart', function(){  });
	 * $observer->addListener('onStop', [$obj, 'methodName']);
	 * $observer->addListener('onStop', 'methodName');
	 *
	 *
	 * @param [type] $args [description]
	 */
	public function addListener(string $messageName, callable $callback)
	{
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
