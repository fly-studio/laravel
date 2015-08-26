<?php

use Illuminate\Database\Eloquent\Model;
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty hook modifier plugin
 *
 * Type:     modifier<br>
 * Name:     hook<br>
 * Purpose:  hook the string according
 * @link http://smarty.php.net/manual/en/language.modifier.hook.php
 *          hook (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param 
 * @return string
 */
function smarty_modifier_hook($value, $model_name, $where_key = NULL)
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

function smarty_modifier_og($value, $key_name)
{
	return ! ($value instanceOf Model) ? $value : $value->$key_name;
}