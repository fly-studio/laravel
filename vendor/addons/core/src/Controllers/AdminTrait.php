<?php
namespace Addons\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

trait AdminTrait {

	private function _getSearcher(&$builder)
	{

	}

	private function _getData(Request $request, Model $model, $callback = NULL, $columns = ['*'])
	{
		$pagesize = $request->input('pagesize') ?: ($request->input('length') ?: config('site.pagesize.admin.'.$model->getTable(), $this->site['pagesize']['common']));
		$page = $request->input('page') ?: (floor(($request->input('start') ?: 0) / $pagesize) + 1);

		$columns = $request->input('columns') ?: [];
		$order = $request->input('order') ?: [];
		//$search = $request->input('search') ?: [];

		$builder = $model->newQuery();
		$this->_getSearcher($builder);
		//!empty($search['value']) && $builder->where('nickname', 'LIKE', '%'.$search['value'].'%');
		foreach ($order as $v)
			!empty($columns[$v['column']]['data']) && $builder->orderBy($columns[$v['column']]['data'], $v['dir']);
		$data = $builder->paginate($pagesize, ['*'], 'page', $page)->toArray();

		!empty($callback) && is_callable($callback) && array_walk($data['data'], $callback);
		
		$data['recordsTotal'] = $model->newQuery()->count();
		$data['recordsFiltered'] = $data['total'];
		return $data;
	}


	private function _getExport(Request $request, Model $model, $callback = NULL, $columns = ['*']) {
		set_time_limit(600); //10min

		$pagesize = $request->input('pagesize') ?: config('site.pagesize.export', 1000);
		$data = $model->newQuery()->orderBy($model->getKeyName(),'DESC')->paginate($pagesize)->toArray();
		!empty($callback) && is_callable($callback) && array_walk($data['data'], $callback);
		!empty($data['data']) && is_assoc($data['data'][0]) && array_unshift($data['data'], array_keys($data['data'][0]));
		array_unshift($data['data'], [$model->getTable(), $data['from']. '-'. $data['to'].'/'. $data['total'], date('Y-m-d h:i:s')]);
		return $data['data'];
	}
}