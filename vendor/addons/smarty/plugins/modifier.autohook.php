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
function smarty_modifier_autohook($value, $model_name)
{
	return model_autohook($value, $model_name);
}
