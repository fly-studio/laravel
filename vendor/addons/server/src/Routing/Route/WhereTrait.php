<?php

namespace Addons\Server\Routing\Route;

trait WhereTrait {

	/**
	 * The regular expression requirements.
	 *
	 * @var array
	 */
	public $wheres = [];

	public function getWheres()
	{
		return $this->wheres;
	}

	/**
	 * Set a regular expression requirement on the route.
	 *
	 * @param  array|string  $name
	 * @param  string  $expression
	 * @return $this
	 */
	public function where($name, $expression = null)
	{
		foreach ($this->parseWhere($name, $expression) as $name => $expression) {
			$this->wheres[$name] = $expression;
		}

		return $this;
	}

	/**
	 * Parse arguments to the where method into an array.
	 *
	 * @param  array|string  $name
	 * @param  string  $expression
	 * @return array
	 */
	protected function parseWhere($name, $expression)
	{
		return is_array($name) ? $name : [$name => $expression];
	}

	/**
	 * Set a list of regular expression requirements on the route.
	 *
	 * @param  array  $wheres
	 * @return $this
	 */
	protected function whereArray(array $wheres)
	{
		foreach ($wheres as $name => $expression) {
			$this->where($name, $expression);
		}

		return $this;
	}
}
