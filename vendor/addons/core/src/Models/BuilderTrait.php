<?php

namespace Addons\Core\Models;

use Addons\Core\Models\Builder;
use Addons\Core\Models\Relations\HasOneBy;
use Addons\Core\Models\Relations\HasManyBy;

trait BuilderTrait {

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newEloquentBuilder($query)
	{
		return new Builder($query);
	}

	/**
	 * Define a one-to-one relationship by a callable.
	 *
	 * @param  callable  $callable
	 * @param  string  $foreignKey
	 * @param  string  $localKey
	 * @return \Addons\Core\Models\Relations\HasOneBy\HasOneBy
	 */
	public function hasOneBy(callable $callable, $foreignKey = null, $localKey = null)
	{
		$localKey = $localKey ?: $this->getKeyName();
		$foreignKey = $foreignKey ?: $this->getForeignKey();

		return new HasOneBy($this, $callable, $foreignKey, $localKey);
	}

	/**
	 * Define a one-to-many relationship by a callable.
	 *
	 * @param  callable  $callable
	 * @param  string  $foreignKey
	 * @param  string  $localKey
	 * @return \Addons\Core\Models\Relations\HasOneBy\HasManyBy
	 */
	public function hasManyBy(callable $callable, $foreignKey = null, $localKey = null)
	{
		$localKey = $localKey ?: $this->getKeyName();
		$foreignKey = $foreignKey ?: $this->getForeignKey();

		return new HasManyBy($this, $callable, $foreignKey, $localKey);
	}

}
