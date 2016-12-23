<?php
namespace Addons\Elasticsearch\Scout;

use Laravel\Scout\Searchable as BaseSearchable;
use Addons\Elasticsearch\Scout\Builder;

trait Searchable {
	use BaseSearchable;

	/**
     * Perform a search against the model's indexed data.
     * @example
     * $query = [
     *     [
     *         'term' => [
     *             'name' => 'admin'
     *         ]
     *     ],
     *     [
     *         'multi_match' => [
     *             'fields' => ['name', 'title'],
     *             'query' => 'admin'
     *         ]
     *     ] 
     * ];
     * search($query);
     * 
     * @example
     * search('admin');
     *
     * @example
     * $query = [
     *     'term' => [
     *         'name' => 'admin'
     *     ]
     * ];
     * search($query);
     * 
     *
     * @param  string|array  $query
     * @param  Closure  $callback
     * @return \Addons\Elasticsearch\Scout\Builder
     */
    public static function search($query = null, $callback = null)
    {
        $builder = new Builder(new static, null, $callback);
        if (is_null($query))
            !is_array($query) ? $builder->where('_all', $query) : $builder->where($query);

        return $builder;
    }

}