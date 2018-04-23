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
 *
 * @author   Fly <fly@load-page.com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_url($string, $nocache = false, $params = [])
{
	$url = url($string, $params, null);
	$nocache && $url .= ((strpos($url, '?') !== false) ? '&' : '?') . '_='. uniqid(date('YmdHis,') . rand(100000, 999999));
	return $url;
}
