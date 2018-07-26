<?php

namespace Addons\Core\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;

trait PolyfillTrait {

	protected $originalCastTypes = ['int', 'integer', 'real' ,'float', 'double', 'string', 'bool', 'boolean', 'object', 'array', 'json', 'collection', 'date', 'datetime', 'timestamp', 'custom_datetime'];

	public static function insertUpdate(array $attributes)
	{
		$model = new static;
		$model->fill($attributes);

		if ($model->usesTimestamps())
			$model->updateTimestamps();


		$attributes = $model->getAttributes();

		$query = $model->newBaseQueryBuilder();
		$processor = $query->getProcessor();
		$grammar = $query->getGrammar();

		$table = $grammar->wrapTable($model->getTable());
		$keyName = $model->getKeyName();
		$columns = $grammar->columnize(array_keys($attributes));
		$insertValues = $grammar->parameterize($attributes);

		$updateValues = [];

		if ($model->getKeyName() !== null)
			$updateValues[] = "{$grammar->wrap($keyName)} = LAST_INSERT_ID({$keyName})";


		foreach ($attributes as $k => $v)
			$updateValues[] = sprintf("%s = '%s'", $grammar->wrap($k), $v);


		$updateValues = join(',', $updateValues);

		$sql = "insert into {$table} ({$columns}) values ({$insertValues}) on duplicate key update {$updateValues}";

		$id = $processor->processInsertGetId($query, $sql, array_values($attributes));

		$model->setAttribute($keyName, $id);

		return $model;
	}

	/**
	 * Cast an attribute to a native PHP type.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function castAttribute($key, $value)
	{
		$type = $this->getCastType($key);
		if (!empty($type) && !in_array($type, $this->originalCastTypes))
		{
			$method = 'as'.Str::studly($type);
			if (method_exists($this, $method))
				return call_user_func([$this, $method], $value, $key, $type);
		}
		return parent::castAttribute($key, $value);
	}

	/**
	 * Convert the model's attributes to an array.
	 *
	 * @return array
	 */
	public function attributesToArray()
	{
		$data = parent::attributesToArray();
		foreach ($this->getCasts() as $key => $type)
		{
			if (!empty($type) && !in_array($type, $this->originalCastTypes) && isset($data[$key]))
			{
				$method = Str::camel($type).'ToArray';
				if (method_exists($this, $method))
					$data[$key] = call_user_func([$this, $method], $data[$key], $key, $type);
			}
		}
		return $data;
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		$type = $this->hasCast($key) ? $this->getCastType($key) : null;
		if (!empty($type) && !$this->hasSetMutator($key)  && !in_array($type, $this->originalCastTypes))
		{
			$method = 'from'.Str::studly($type);
			if (method_exists($this, $method))
				$value = call_user_func([$this, $method], $value, $key, $type);
		}
		return parent::setAttribute($key, $value);
	}

	public function toArray(array $options = [])
	{
		$options += [
			'cast' => true,
			'append' => true,
			'relations' => true,
			'dateFormat' => null,
		];

		$data = $this->getArrayableAttributes();

		$data = $this->addMutatedAttributesToArray(
			$data, $mutatedAttributes = $this->getMutatedAttributes()
		);

		if ($options['cast'])
		{
			foreach ($this->getCasts() as $key => $value) {

				if (! isset($data[$key]) || in_array($key, $mutatedAttributes)) {
					continue;
				}

				$data[$key] = $this->castAttribute(
					$key, $data[$key]
				);

				if ($data[$key] &&
					($value === 'date' || $value === 'datetime')) {
					$data[$key] = $this->serializeDate($data[$key]);
				}

				if ($data[$key] && $this->isCustomDateTimeCast($value)) {
					$data[$key] = $data[$key]->format(explode(':', $value, 2)[1]);
				}
			}
		}

		if ($options['append'])
		{
			foreach ($this->getArrayableAppends() as $key) {
				$data[$key] = $this->mutateAttributeForArray($key, null);
			}
		}

		$dateFormat = !empty($options['dateFormat']) ? $options['dateFormat'] : $this->getDateFormat();

		foreach ($this->getDates() as $key) {
			if (! isset($data[$key]) || empty($data[$key])) {
				continue;
			}

			$data[$key] = $this->asDateTime($data[$key])->format($dateFormat);
		}

		$attributes = [];

		if ($options['relations'])
		{
			foreach ($this->getArrayableRelations() as $key => $value) {

				if ($value instanceof Model) {
					$relation = $value->toArray($options);
				} else if ($value instanceof Arrayable) {
					$relation = $value->toArray($options);
				} else if (is_null($value)) {
					$relation = $value;
				}

				if (static::$snakeAttributes) {
					$key = Str::snake($key);
				}

				// If the relation value has been set, we will set it on this attributes
				// list for returning. If it was not arrayable or null, we'll not set
				// the value on the array because it is some type of invalid value.
				if (isset($relation) || is_null($value)) {
					$attributes[$key] = $relation;
				}

				unset($relation);
			}
		}

		return $data + $attributes;
	}

	public function toArrayWith(array $with = [], array $options = [])
	{
		!empty($with) && $this->loadMissing($with);

		return $this->toArray($options);
	}

}
