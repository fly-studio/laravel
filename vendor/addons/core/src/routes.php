<?php

$router->get('attachment/{id}/{filename}', function($id, $filename){
	$class = new Addons\Core\Controllers\AttachmentController;
	return $class->index($id);
});

$router->resource('manual', '\\Addons\\Core\\Controllers\\ManualController');