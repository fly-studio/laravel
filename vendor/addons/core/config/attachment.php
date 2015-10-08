<?php

return [
	'remote' => [
		'enabled' => FALSE,
		'host' => 'SSH\'s ip', //ip only
		//'host_fingerprint' => NULL,
		'port' => 22,
		'authentication_method' => 'PASS', //PASS KEY 
		'user' => NULL, //set it if authentication_method == 'PASS'
		'password' => NULL, //set it if authentication_method == 'PASS'
		//'pub_key' => NULL, //set it if authentication_method == 'KEY'
		//'private_key' => NULL, //set it if authentication_method == 'KEY'
		//'passphrase' => NULL, //set it if authentication_method == 'KEY'
		'auto_connect' => TRUE,
		'path' => '/path/to/attachments/', //远程存放的路径
		'file_own' => NULL, //文件所属用户，比如：nobody
		'file_grp' => NULL, //文件所属组，比如：nobody
		'file_mod' => 0644, //文件的权限
		'folder_own' => NULL, //文件夹所属用户，比如：nobody
		'folder_grp' => NULL, //文件夹所属组，比如：nobody
		'folder_mod' => 0777, //文件夹的权限，一般情况下必须要777
	],
	'local' => [
		'enabled' => TRUE,
		'life_time' => 0, //enabled为TRUE时无效，0表示永不过期，
		'path' => 'attachments'.DIRECTORY_SEPARATOR, //本地存放路径
		'file_own' => NULL, //文件所属用户，比如：nobody
		'file_grp' => NULL, //文件所属组，比如：nobody
		'file_mod' => 0644,
		'folder_own' => NULL, //文件夹所属用户，比如：nobody
		'folder_grp' => NULL, //文件夹所属组，比如：nobody
		'folder_mod' => 0777, //文件夹的权限，一般情况下必须要777
	],
	'ext' => ['mov','ogg','tp','ts','mkv','webm','webp','rmvb','rm','asf','mpeg','mpg','avi','midi','mid','wmv','wma','wav','mp4','mp3','amr','f4v','flv','swf','bz2','gz','pptx','ppt','xslx','xsl','docx','doc','pdf','7z','rar','zip','gif','png','bmp','jpeg','jpg'],
	'maxsize' => 1024 * 1024 * 100,  //100M
	'normal_ext' => 'gf',
	'file_type' => [
		'text' => [
			'php','php5','phps',
			'html','htm','shtm','shtml','tpl',
			'htaccess',
			'js','vbs',
			'css','less','cass',
			'asp','aspx',
			'c','cpp','cs',
			'h','hpp',
			'sql',
			'txt','text',
			'log',
			'cache',
		],
		'image' => [
			'jpg','jpeg',
			'bmp',
			'gif',
			'png',
			'webp',
		],
		'video' => [
			'mov',
			'tp','ts',
			'mkv',
			'webm',
			'mp4','mpeg','mpg',
			'wmv',
			'avi',
			'rm','rmvb',
			'asf',
			'f4v','flv',
			'swf',
		],
		'audio' => [
			'mp3',
			'ogg',
			'wma',
			'amr',
			'mid','midi',
		],
		'archive' => [
			'7z','001','002','003','004','005',
			'rar',
			'zip','bz2','gz','tar'
		],
		'document' => [
			'pptx','ppt',
			'xslx','xsl',
			'docx','doc',
			'pdf',
		],
	],
];