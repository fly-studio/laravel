<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty plugins modifier plugin
 *
 * Type:     modifier<br>
 * Name:     plugins<br>
 * Purpose:  get the absolute URL of plugins file
 *
 * @author   Fly <fly@load-page.com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_plugins($url)
{
	return str_replace('index.php', '', plugins_url($url));
}

?>
