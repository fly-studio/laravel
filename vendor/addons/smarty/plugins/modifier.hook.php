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
	return model_hook($value, $model_name, $where_key);
}

function smarty_modifier_og($value, $key_name)
{
	return model_og($value, $key_name);
}