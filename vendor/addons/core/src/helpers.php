<?php

if (! function_exists('delay_unlink')) {
	function delay_unlink(string $path, int $delay)
	{
		return;
		if (!file_exists($path)) return FALSE;

		$md5 = is_file($path) ? md5_file($path) : NULL;

		//Queue
		$job = (new \Addons\Core\Jobs\DelayUnlink($path, $md5))->delay($delay);
		app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
	}
}

if (! function_exists('static_path')) {
	function static_path(string $path = '')
	{
		return normalize_path(config('app.static_path') . (!empty($path) ? DIRECTORY_SEPARATOR.$path : ''));
	}
}

if (! function_exists('utils_path'))
{
	function utils_path(string $path = '')
	{
		return normalize_path(config('app.utils_path') . (!empty($path) ? DIRECTORY_SEPARATOR.$path : ''));
	}
}

if (! function_exists('static_url')) {
	function static_url(string $url = '')
	{
		static $static;

		if (empty($static))
		{
			$static = trim(
				str_replace([public_path(), '\\'], ['', '/'], static_path()),
				'/'
			);
		}

		return url($static . (!empty($url) ? '/'.$url : ''));
	}
}

if (! function_exists('repo')) {
	function repo(string $className)
	{
		return app('App\\Repositories\\'.studly_case($className).'Repository');
	}
}
