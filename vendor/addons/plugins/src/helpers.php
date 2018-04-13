<?php

if (! function_exists('plugins_path')) {
	function plugins_path($path = '')
	{
		return static_path('plugins'.DIRECTORY_SEPARATOR.$path);
	}
}

if (! function_exists('plugins_url')) {
	function plugins_url($url = '')
	{
		return static_url('plugins/'.$url);
	}
}

if (! function_exists('plugins_config')) {
	function plugins_config($pluginName, $configName = null)
	{
		return config('plugins.'.$pluginName.(is_null($configName) ? '' : '.'.$configName));
	}
}

if (! function_exists('plugins_repo')) {
	function plugins_repo($pluginName, $className)
	{
		return app(plugins_config($pluginName, 'namespace').'\\App\\Repositories\\'.studly_case($className).'Repository');
	}
}
