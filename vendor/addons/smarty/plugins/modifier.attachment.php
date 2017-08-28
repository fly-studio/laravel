<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty attachment modifier plugin
 *
 * Type:     modifier<br>
 * Name:     attachment<br>
 * Purpose:  get the absolute URL of attachment
 *
 * @author   Fly <fly@load-page.com>
 * @param string
 * @param string
 * @param array
 * @return string
 */
function smarty_modifier_attachment($id, $params = [], $route = 'attachment')
{
	return url()->route($route, $params + ['id' => $id]);
}
