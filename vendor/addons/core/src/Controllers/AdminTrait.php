<?php
namespace Addons\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
trait AdminTrait {

	private function _doFilter(Request $request, Builder $builder)
	{
		$filters = $this->_getFilters($request, $builder);
		$operators = [
			'in' => 'in', 'not_in' => 'not in', 'is' => 'is', 'min' => '>=', 'greater_equal' => '>=', 'max' => '<=', 'less_equal' => '<=', 'between' => 'between', 'not_between' => 'not between', 'greater' => '>', 'less' => '<', 'not_equal' => '<>', 'inequal' => '<>', 'equal' => '=',
			'like' => 'like', 'left_like' => 'like', 'right_like' => 'like', 'rlike' => 'rlike', 'ilike' => 'ilike', 'like_binary' => 'like binary', 'left_like_binary' => 'like binary', 'right_like_binary' => 'like binary', 'not_like' => 'not like', 'not_left_like' => 'not like', 'not_right_like' => 'not like',
			'and' => '&', 'or' => '|', 'xor' => '^', 'left_shift' => '<<', 'right_shift' => '>>', 'bitwise_not' => '~', 'bitwise_not_any' => '~*', 'not_bitwise_not' => '!~', 'not_bitwise_not_any' => '!~*',
			'regexp' => 'regexp', 'not_regexp' => 'not regexp', 'similar_to' => 'similar to', 'not_similar_to' => 'not similar to',
		];

		array_walk($filters, function($v, $key) use ($builder, $operators) {
			array_walk($v, function($value, $method) use ($builder, $key, $operators){
				if (empty($value)) return;
				else if (in_array($method, ['like', 'like_binary', 'not_like'])) $value = '%'.$value.'%';
				else if (in_array($method, ['left_like', 'left_like_binary', 'not_left_like'])) $value = $value.'%';
				else if (in_array($method, ['right_like', 'right_like_binary', 'not_right_like'])) $value = '%'.$value;
				$builder->where($key, $operators[$method] ?: '=' , $value);
			});
		});
		return $filters;
	}

	private function _doOrder(Request $request, Builder $builder)
	{
		$orders = $this->_getOrders($request, $builder);
		foreach ($orders as $k => $v)
			$builder->orderBy($k, $v);
		return $orders;
	}

	private function _getFilters(Request $request, Builder $builder)
	{
		$filters = [];
		$inputs = $request->input('filter') ?: $request->except('of', 'base','draw','columns','order','pagesize', 'length', 'start', 'page', 'search');
		foreach ($inputs as $k => $v)
			$filters[$k] = is_array($v) ? array_change_key_case($v) : ['equal' => $v];

		return $filters;
	}

	private function _getOrders(Request $request, Builder $builder)
	{
		$columns = $request->input('columns') ?: [];
		$inputs = $request->input('order') ?: [];

		$orders = [];
		if(!empty($columns))
			foreach ($inputs as $v)
				!empty($columns[$v['column']]['data']) && $orders[$columns[$v['column']]['data']] = strtolower($v['dir']); 
		else
			$orders = $inputs;

		return $orders;
	}

	private function _getPaginate(Request $request, Builder $builder, array $columns = ['*'], array $extra_query = [])
	{
		$pagesize = $request->input('pagesize') ?: ($request->input('length') ?: config('site.pagesize.admin.'.$builder->getModel()->getTable(), $this->site['pagesize']['common']));
		$page = $request->input('page') ?: (floor(($request->input('start') ?: 0) / $pagesize) + 1);

		$filters = $this->_doFilter($request, $builder);
		$orders = $this->_doOrder($request, $builder);

		$paginate = $builder->paginate($pagesize, $columns, 'page', $page);

		$query = $filters + $extra_query;
		array_walk($query, function($v, $k) use($paginate) {
			$paginate->addQuery($k, $v);
		});

		return $paginate;
	}

	private function _getData(Request $request, Builder $builder, $callback = NULL, array $columns = ['*'])
	{
		$data = $this->_getPaginate($request, $builder)->toArray();

		!empty($callback) && is_callable($callback) && array_walk($data['data'], $callback);

		return $data;
	}


	private function _getExport(Request $request, Builder $builder, $callback = NULL, array $columns = ['*']) {
		set_time_limit(600); //10min

		$pagesize = $request->input('pagesize') ?: config('site.pagesize.export', 1000);
		$data = $builder->orderBy($builder->getModel()->getKeyName(),'DESC')->paginate($pagesize)->toArray();
		!empty($callback) && is_callable($callback) && array_walk($data['data'], $callback);
		!empty($data['data']) && is_assoc($data['data'][0]) && array_unshift($data['data'], array_keys($data['data'][0]));
		array_unshift($data['data'], [$builder->getModel()->getTable(), $data['from']. '-'. $data['to'].'/'. $data['total'], date('Y-m-d h:i:s')]);
		return $data['data'];
	}
}