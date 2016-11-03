<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty static modifier plugin
 *
 * Type:     modifier<br>
 * Name:     static<br>
 * Purpose:  get the absolute URL of static file
 *
 * @author   Fly <fly@load-page.com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_static($url, $multi = FALSE)
{
	return str_replace('index.php', '', static_url($url));
}

