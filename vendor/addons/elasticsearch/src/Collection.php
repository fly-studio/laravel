<?php

namespace Addons\Elasticsearch;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

class Collection extends BaseCollection {

	protected $model = null;

	public function setModelName($model)
	{
		$this->model = is_string($model) ? $model : get_class($model);
		return $this;
	}

	public function asModels() : ModelCollection
	{
		$collection = $this->map(function($v) {
			return $v instanceof Model ? $v : (new $this->model)->setDateFormat(\DateTime::W3C)->forceFill($v)->syncOriginal();
		});

		return ModelCollection::make($collection->all());
	}

	public function existsInDB(): Collection
	{
		$keys = $this->pluck('id');

		if (empty($keys)) return new static();

		$model = new $this->model;

		$models = $model->newQuery()->whereIn($model->getKeyName(), $keys)->get([$model->getKeyName()])->modelKeys();

		return $this->filter(function($v){
			return in_array($v['id'], $models);
		});
	}

	public function load($relations)
	{
		return $this->asModels()->load($relations);
	}

}
