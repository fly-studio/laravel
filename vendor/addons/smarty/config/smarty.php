<?php
return [
	// Smarty模板文件拓展名。
	'extension' => 'tpl',

	'debugging' => false,
	'caching' => false,
	'cache_lifetime' => 60,
	'compile_check' => true,

	// 相关路径配置。
	'template_path' => [base_path('resources/views'), __DIR__.'/../../core/resources/views'],
	'cache_path' => storage_path('smarty/cache'),
	'compile_path' => storage_path('smarty/compile'),
	'plugins_paths' => [
		base_path('resources/smarty/plugins'),
		realpath(__DIR__.'/../plugins'),
	],
	'config_paths' => [
		base_path('resources/smarty/config')
	],

	// use different delimiters throughout your templates if you really want to!
	'left_delimiter'  =>  '<{',
	'right_delimiter' =>  '}>',


	// 当系统缓存驱动不为“file”时有效。
	'cache_prefix' => 'smarty',

	// escape HTML: if this is set to true, then all variables will be escaped.
	// See http://www.smarty.net/docs/en/variable.escape.html.tpl
	//
	'escape_html' => true,
];
