<?php

namespace Addons\Func\Contracts;

trait BootTrait {

	protected $booted = false;

	public function boot(...$args)
	{
		if ($this->booted) return;

		$classes = array_merge([get_class($this)], parent_class_recursive($this));

		foreach(array_reverse($classes) as $class)
		{
			$boot = 'boot'.basename(str_replace('\\', DIRECTORY_SEPARATOR, $class));
			if (method_exists($this, $boot))
				call_user_func_array([$this, $boot], $args);
		}

		$this->booted = true;
	}
}
