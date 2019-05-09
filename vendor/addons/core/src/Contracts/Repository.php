<?php

namespace Addons\Core\Contracts;

use Addons\Core\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class Repository {
	use ApiTrait;

	abstract public function prePage();
	abstract public function find($id, $columns = ['*']);
	abstract public function store(array $data);
	abstract public function update(Model $model, array $data);
	abstract public function destroy(array $ids);
	abstract public function data(Request $request, $columns = ['*']);
	abstract public function export(Request $request, $columns = ['*']);
}
