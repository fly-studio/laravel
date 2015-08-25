<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  Escape the string according to escapement type
 * @link http://smarty.php.net/manual/en/language.modifier.escape.php
 *          escape (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param html|htmlall|url|quotes|hex|hexentity|javascript|noscript|nohtml
 * @return string
 */
function smarty_modifier_url($string, $nocache = FALSE)
{
    $url = url($string, [], NULL);
    $nocache && $url .= ((strpos($url, '?') !== FALSE) ? '&' : '?') . '_='. uniqid(date('YmdHis,') . rand(100000,999999)); 
    return $url;
}

?>
