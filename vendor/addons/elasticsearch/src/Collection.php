<?php

namespace Addons\Elasticsearch;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

class Collection extends BaseCollection {

	public function asDepthArray()
	{
		foreach($this->items as $k => $v)
		{
			$r = [];
			foreach($v as $key => $value)
				array_set($r, $key, $value);

			$this->items[$k] = $r;
		}

		return $this;
	}

	public function toModels($model, array $relations = [])
	{
		$model = is_string($model) ? $model : get_class($model);

		$results = [];

		foreach($this->items as $k => $v)
		{
			if (!($v instanceof Model))
			{
				$model = new $this->model;

				foreach ($model->getDates() as $key) {
					if (! isset($v[$key]) || empty($v[$key]))
						continue;

					$time = strtotime($v[$key]);

					$v[$key] = $time === false ? $v[$key] : Carbon::createFromTimestamp($time);
				}

				$model->setRawAttributes(array_except($v, $relations));

				foreach($relations as $relation)
					$model->setRelation($relation, array_get($v, $relation));

				$results[$k] = $model;
			} else {
				$results[$k] = $v;
			}
		}

		return ModelCollection::make($results);
	}

	public function filterWithDB($model): Collection
	{
		$model = is_string($model) ? $model : get_class($model);

		$keys = $this->pluck('id');

		if (empty($keys)) return new static();

		$model = new $this->model;

		$models = $model->newQuery()->whereIn($model->getKeyName(), $keys)->get([$model->getKeyName()])->modelKeys();

		return $this->filter(function($v){
			return in_array($v['id'], $models);
		});
	}

}
