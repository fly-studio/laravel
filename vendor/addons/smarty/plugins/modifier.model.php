<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty model modifier plugin
 *
 * Type:     modifier<br>
 * Name:     model<br>
 * Purpose:  model the string according
 * @link http://smarty.php.net/manual/en/language.modifier.model.php
 *          model (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param 
 * @return string
 */

function smarty_modifier_model($value, $key_name)
{
	return model_get($value, $key_name);
}