<?php
use Addons\Core\Models\Attachment;
return [

	/*
	|--------------------------------------------------------------------------
	| Common Language Lines
	|--------------------------------------------------------------------------
	|
	| 
	| 
	| 
	|
	*/

	'default' => [
		'success' => [
			'title' => '操作成功',
			'content' => '您的操作已经成功！',
		],
		'error' => [
			'title' => '操作错误',
			'content' => '您的操作错误，请重试！',
		],
		'failure' => [
			'title' => '操作失败',
			'content' => '您的操作失败，请重试！',
		],
		'warning' => [
			'title' => '操作警告',
			'content' => '您的操作不正确，请重试！',
		],
		'notice' => [
			'title' => '提示',
			'content' => '请阅读并理解提示信息！',
		],
	],
	'server' => [
		'error_param' => [
			'title' => '参数错误',
			'content' => '您传递的参数错误，请检查您的来路是否正确！',
		],
		'error_referrer' => [
			'title' => '请求来源错误',
			'content' => '您的请求来源[:referrer]不在许可范围内！',
		],
		'error_server' => [
			'title' => '服务器内部错误',
			'content' => '服务器内部错误，请稍后再试！',
		],
		'error_database' => [
			'title' => '数据库错误',
			'content' => '服务器数据库出现错误，请稍后再试！',
		],
	],
	'validation' => [
		'failure_post' => [
			'title' => '提交资料失败',
			'content' => '<ul class="post_faiure">:messages</ul>',
			'list' => '<li>:message</li>'.PHP_EOL,
		],
	],
	'auth' => [
		'success_login' => [
			'title' => '登录成功',
			'content' => '即将跳转到刚才的页面！',
		],
		'success_logout' => [
			'title' => '登出成功',
			'content' => '即将跳转到刚才的页面！',
		],
		'failure_permission' => [
			'title' => '权限不够',
			'content' => '您的权限不够，无法访问本页面，切换用户请<a href="'.url('auth').'" target="_self">点击这里</a>！',
		],
		'failure_login' => [
			'title' => '登录失败',
			'content' => '账号或密码错误！',
		],
		'failure_unlogin' => [
			'title' => '您尚未登录',
			'content' => '您尚未登录，无法访问本页面，登录请<a href="'.url('auth').'" target="_self">点击这里</a>！',
		],
	],
	'document' => [
		'failure_noexist' => [
			'title' => '资料不存在',
			'content' => '您要查找的资料不存在！',
		],
		'failure_owner' => [
			'title' => '越权',
			'content' => '您无法查看或修改他人的资料！',
		],
	],
	'attachment' => [
		'failure_noexists' => [
			'title' => '附件不存在',
			'content' => '该附件不存在，或者已被删除！',
		],
		'success_upload' => [
			'title' => '上传成功',
			'content' => '您的文件已经上传成功！',
		],
		'success_download' => [
			'title' => '下载成功',
			'content' => '您的文件已经下载成功！',
		],
		'failure_resize' => [
			'title' => '无法裁减',
			'content' => '您的文件非图片类型，无法裁减！',
		],
		UPLOAD_ERR_OK => [
			'title' => '上传成功',
			'content' => '文件上传成功。',
		],
		UPLOAD_ERR_INI_SIZE => [
			'title' => '上传失败',
			'content' => '上传的文件超过了 php.ini 中 upload_max_filesize ('.ini_get('upload_max_filesize').'B) 选项限制的值。 ',
		],
		UPLOAD_ERR_FORM_SIZE => [
			'title' => '上传失败',
			'content' => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。',
		],
		UPLOAD_ERR_PARTIAL => [
			'title' => '上传失败',
			'content' => '文件不完整，只有部分被上传。',
		],
		UPLOAD_ERR_NO_FILE => [
			'title' => '上传失败',
			'content' => '没有文件被上传。',
		],
		UPLOAD_ERR_NO_TMP_DIR => [
			'title' => '上传失败',
			'content' => '系统错误，找不到临时文件夹。',
		],
		UPLOAD_ERR_CANT_WRITE => [
			'title' => '上传失败',
			'content' => '系统错误，临时文件写入失败。',
		],
		Attachment::UPLOAD_ERR_MAXSIZE => [
			'title' => '上传/下载失败',
			'content' => '文件大小超出:maxsize字节！',
		],
		Attachment::UPLOAD_ERR_EMPTY => [
			'title' => '上传/下载失败',
			'content' => '文件不能为空！',
		],
		Attachment::UPLOAD_ERR_EXT => [
			'title' => '上传/下载失败',
			'content' => '不合法的文件类型，请上传/下载常规的文件，以下是允许的文件类型：:ext',
		],
		Attachment::UPLOAD_ERR_SAVE => [
			'title' => '保存文件失败',
			'content' => '请检查目录是否有写入权限，或者检查远程服务器配置是否正确。',
		],
		Attachment::DOWNLOAD_ERR_URL => [
			'title' => '下载失败',
			'content' => '下载的URL无效。',
		],
		Attachment::DOWNLOAD_ERR_FILE => [
			'title' => '下载失败',
			'content' => '服务器无响应，无法下载此URL。',
		],
	],
];
