<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty URL modifier plugin
 *
 * Type:     modifier<br>
 * Name:     url<br>
 * Purpose:  get the absolute URL
 * @link http://smarty.php.net/manual/en/language.modifier.url.php
 *          url (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_url($string, $nocache = FALSE)
{
	$url = url($string, [], NULL);
	$nocache && $url .= ((strpos($url, '?') !== FALSE) ? '&' : '?') . '_='. uniqid(date('YmdHis,') . rand(100000,999999)); 
	return $url;
}

?>
