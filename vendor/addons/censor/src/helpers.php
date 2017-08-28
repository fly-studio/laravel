<?php

if (! function_exists('censor')) {
	function censor($key, $attributes, $replace = [])
	{
		return app('censor')->make($key, $attributes, $replace);
	}
}

if (! function_exists('validator')) {
	function validator($data, $key, $attributes, $replace = [])
	{
		return censor($key, $attributes, $replace)->data($data)->validator();
	}
}

