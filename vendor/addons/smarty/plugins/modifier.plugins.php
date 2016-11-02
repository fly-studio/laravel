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
function smarty_modifier_plugins($string)
{
	static $static;
	if (empty($static)) $static = config('app.static');
	$urls = explode(',', $string);
	foreach($urls as &$url)
		$url = 'plugins/'.$url;
	$string = implode(',', $urls);

	$url = url($static . $string);
	return $url;
}

?>
