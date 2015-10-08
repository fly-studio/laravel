<?php
if (!function_exists('model_hook'))
{
function model_hook($value, $model_name, $where_key = NULL)
{
	static $data;
	$model_name = ucfirst($model_name);
	if (isset($data[$model_name][$where_key][$value])) return $data[$model_name][$where_key][$value];
	
	$class_name = 'App\\'.$model_name;
	if (!class_exists($class_name)) return $value;

	$class = new $class_name;
	$_data = empty($where_key) ? $class->find($value) : $class->where($where_key, $value)->first();
	return $data[$model_name][$where_key][$value] = (empty($_data) ? $value : $_data);
}
function model_get($value, $key_name)
{
	return ! ($value instanceOf Illuminate\Database\Eloquent\Model) ? $value : $value->$key_name;
}
function model_autohook($value, $model_name)
{
	static $data;
	if (isset($data[$model_name][$value])) return $data[$model_name][$value];
	$v = model_hook($value, $model_name);
	if ($v instanceOf Illuminate\Database\Eloquent\Model){
		if (isset($v['name'])) $data[$model_name][$value] = $v['name'];
		else if (isset($v['title']))  $data[$model_name][$value] = $v['title'];
		else if (isset($v['text']))  $data[$model_name][$value] = $v['text'];
		else if (isset($v['username'])) $data[$model_name][$value] = $v['username'];
		else $data[$model_name][$value] = $v->getKey();
	} else $data[$model_name][$value] = $v;
	return $data[$model_name][$value];
}
}
if (!function_exists('delay_unlink'))
{
function delay_unlink($path, $delay)
{
	if (!file_exists($path)) return FALSE;

	$md5 = !is_dir($path) ? md5_file($path) : NULL;
	//Queue
	$job = (new Addons\Core\Jobs\DelayUnlink($path, $md5))->delay($delay);
	app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
}
}