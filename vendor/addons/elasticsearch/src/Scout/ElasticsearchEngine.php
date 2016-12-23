<?php
namespace Addons\Elasticsearch\Scout;

use Laravel\Scout\Engines\ElasticsearchEngine as BaseElasticsearchEngine;
//use Addons\Elasticsearch\Scout\Builder;
use Laravel\Scout\Builder;
class ElasticsearchEngine extends BaseElasticsearchEngine {

	/**
     * Get the filter array for the query.
     *
     * @param  Builder  $query
     * @return array
     */
    protected function filters(Builder $query)
    {
        return $query->wheres->toArray();
    }
	/**
     * Perform the given search on the engine.
     *
     * @param  Builder  $query
     * @return mixed
     */
    public function search(Builder $query)
    {
        return $this->performSearch($query, [
            'filters' => $this->filters($query),
            'size' => $query->limit ?: 10000,
        ]);
    }

	/**
     * Perform the given search on the engine.
     *
     * @param  Builder  $query
     * @return mixed
     */
    public function count(Builder $query)
    {
        $result = $this->performCount($query, [
            'filters' => $this->filters($query),
        ]);
        return isset($result['count']) ? $result['count'] : false;
    }

	

	protected function performCount(Builder $builder, array $options = [])
	{
		$query = [
			'index' =>  $this->index,
			'type'  =>  $builder->model->searchableAs(),
			'body' => [
				'query' => $options['filters'],
			],
		];

		return $this->elasticsearch->count($query);
	}

	/**
	 * Perform the given search on the engine.
	 * Parent::performSearch dont support elastic 5.x
	 *
	 * @param  Builder  $builder
	 * @param  array  $options
	 * @return mixed
	 */
	protected function performSearch(Builder $builder, array $options = [])
	{
		
		$query = [
			'index' =>  $this->index,
			'type'  =>  $builder->model->searchableAs(),
			'body' => [
				'_source' => $builder->_source,
				'query' => $options['filters'],
			],
		];


		if (array_key_exists('size', $options)) {
			$query['size'] = $options['size'];
		}

		if (array_key_exists('from', $options)) {
			$query['from'] = $options['from'];
		}

		if ($builder->callback) {
			return call_user_func(
				$builder->callback,
				$this->elasticsearch,
				$query
			);
		}

		return $this->elasticsearch->search($query);
	}

	/**
     * Get the results of the query as a Collection of primary keys.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return \Illuminate\Support\Collection
     */
    public function keys(Builder $builder)
    {
    	$builder->_source = false; //elastic return no _source
        return $this->getIds($this->search($builder));
    }
}