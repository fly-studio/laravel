<?php

namespace Addons\Func\Contracts;

use Addons\Func\Contracts\TraitsBootTrait;

trait ClassesBootTrait {

	use TraitsBootTrait;

	/**
	 * The "booting" method of the class.
	 *
	 * @return void
	 */

	public function boot(...$args)
	{
		$classes = array_reverse(array_merge([static::class], parent_class_recursive($this)));

		foreach($classes as $class)
			if (method_exists($this, $method = 'boot'.class_basename($class)))
				call_user_func_array([$this, $method], $args);
	}
}
