<?php

if (! function_exists('plugins_path')) {
	function plugins_path(string $path = '')
	{
		return static_path('plugins'.DIRECTORY_SEPARATOR.$path);
	}
}

if (! function_exists('plugins_url')) {
	function plugins_url(string $url = '')
	{
		return static_url('plugins/'.$url);
	}
}

if (! function_exists('plugins_config')) {
	function plugins_config(string $pluginName, string $configName = null)
	{
		return config('plugins.'.$pluginName.(is_null($configName) ? '' : '.'.$configName));
	}
}

if (! function_exists('plugins_repo')) {
	function plugins_repo(string $pluginName, string $className)
	{
		return app(plugins_config($pluginName, 'namespace').'\\App\\Repositories\\'.studly_case($className).'Repository');
	}
}
