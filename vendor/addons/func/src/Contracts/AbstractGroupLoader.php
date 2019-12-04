<?php

namespace Addons\Func\Contracts;

use Closure;
use Illuminate\Support\Arr;

abstract class AbstractGroupLoader {

	/**
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected $groupStack = [];

	protected $loadResolver;

	public function setLoadResolver(Closure $callback)
	{
		$this->loadResolver = $callback;
	}

	public function getLoadResolver()
	{
		return $this->loadResolver ?: function($file_path, $loader) {
			require realpath($file_path);
		};
	}

	public function load(string $file_path, string $namespace = 'App')
	{
		$this->group(['namespace' => $namespace], function($loader) use ($file_path) {
			return call_user_func($this->getLoadResolver(), $file_path, $loader);
		});
	}

	public function group(array $attributes, $callback)
	{
		$this->updateGroupStack($attributes);

		if ($callback instanceof Closure)
			call_user_func($callback, $this);
		else
			call_user_func($this->getLoadResolver(), $callback, $this);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
		if (! empty($this->groupStack)) {
			$attributes = $this->mergeGroup($attributes, end($this->groupStack));
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given array with the last group stack.
	 *
	 * @param  array  $new
	 * @return array
	 */
	public function mergeWithLastGroup(array $new) : array
	{
		return $this->mergeGroup($new, end($this->groupStack));
	}

	/**
	 * Prepend the last group namespace onto the use clause.
	 *
	 * @param  string  $class
	 * @return string
	 */
	protected function prependGroupNamespace(string $class) : string
	{
		$group = end($this->groupStack);

		return isset($group['namespace']) && strpos($class, '\\') !== 0
				? $group['namespace'].'\\'.$class : $class;
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	public static function mergeGroup(array $new, array $old) : array
	{
		$new['namespace'] = static::formatUsesPrefix($new, $old);

		return array_merge_recursive(Arr::except($old, ['namespace']), $new);
	}

	/**
	 * Determine if the eventer currently has a group stack.
	 *
	 * @return bool
	 */
	public function hasGroupStack() : bool
	{
		return ! empty($this->groupStack);
	}

	/**
	 * Get the current group stack for the eventer.
	 *
	 * @return array
	 */
	public function getGroupStack() : array
	{
		return $this->groupStack;
	}

	/**
	 * Format the uses prefix for the new group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return string|null
	 */
	protected static function formatUsesPrefix(array $new, array $old) : string
	{
		if (isset($new['namespace'])) {
			return isset($old['namespace'])
					? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
					: trim($new['namespace'], '\\');
		}

		return isset($old['namespace']) ? $old['namespace'] : null;
	}

}
