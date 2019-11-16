<?php

namespace Addons\Core\Controllers\Concerns;

use BadMethodCallException;
use Addons\Core\Http\Output\ResponseFactory;
use Addons\Core\Exceptions\OutputResponseException;

trait OutputResponseTrait {

	public function api($data, $encrypted = false, string $rsaType = 'public')
	{
		return app(ResponseFactory::class)->make('api', ...func_get_args());
	}

	public function office(?array $data)
	{
		return app(ResponseFactory::class)->make('office', ...func_get_args());
	}

	public function success(string $messageName = null, $data = null)
	{
		return app(ResponseFactory::class)->make('success', ...func_get_args());
	}

	public function error(string $messageName = null, $data = null)
	{
		return app(ResponseFactory::class)->make('error', ...func_get_args());
		//抛出失败，终止运行
		//throw new OutputResponseException($response);
	}
}
