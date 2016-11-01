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
function smarty_modifier_static($string, $rewrite = FALSE)
{
	$static = env('STATIC_PATH', '/static');
	
	if ($rewrite)
	{
		$urls = explode(',', $string);
		foreach($urls as &$url)
			if (!file_exists(APPPATH.$static.'/'.$url))
				$url = $static.'/common/'.$url;
		$string = implode(',', $urls);
	}

	$url = url($static.'/' . $string);
	return $url;
}

?>
