<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty width_truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     width_truncate<br>
 * Purpose:  width_truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */

use Smarty;

function smarty_modifier_width_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
{
	if ($length == 0)
		return '';
	$ansi_as = 2;
	if (strlen_ansi($string, Smarty::$_CHARSET, $ansi_as) > $length) {
		$length -= min($length, strlen_ansi($etc, Smarty::$_CHARSET, $ansi_as));
		if (!$break_words && !$middle) {
			$string = preg_replace('/\s+?(\S+)?$/' . Smarty::$_UTF8_MODIFIER, '', substr_ansi($string, 0, $length+1, Smarty::$_CHARSET, $ansi_as));
		}
		if(!$middle) {
			return substr_ansi($string, 0, $length,  Smarty::$_CHARSET, $ansi_as) . $etc;
		} else {
			return substr_ansi($string, 0, $length/2, Smarty::$_CHARSET,$ansi_as) . $etc . substr_ansi($string, -$length/2, 0, Smarty::$_CHARSET, $ansi_as);
		}
	} else {
		return $string;
	}
}

/* vim: set expandtab: */
