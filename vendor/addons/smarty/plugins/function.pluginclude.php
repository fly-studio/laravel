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
 * Name:     plugins<br>
 * Purpose:  get the absolute URL
 * @link http://smarty.php.net/manual/en/language.modifier.url.php
 *          url (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param mixed
 * @return string
 */
function smarty_function_pluginclude($params, $template)
{
	$_c = config('plugins.plugins');
	if (empty($_c)) return;

	$dbt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
	for($i = 1; $i < count($dbt);$i++)
		if ($dbt[$i]['function'] == __FUNCTION__)
		{
			trigger_error("pluginclude: cannot call 'pluginclude' in 'pluginclude' (nested)", E_USER_NOTICE);
			return;
		}

	$file = '';
	$plugins = NULL;
	foreach ($params as $_key => $_val) {
		switch ($_key) {
			case 'file':
			case 'plugins':
				$$_key = $_val;
			break;
		}
	}

	if (empty($file)) {
		trigger_error("pluginclude: missing 'file' parameter", E_USER_NOTICE);
		return;
	}
	!empty($plugins) && !is_array($plugins) && $plugins = explode(',', $plugins);
	!empty($plugins) && $_c = array_only($_c, $plugins);

	$names = [];
	foreach ($_c as $k => $v) {
		if (array_key_exists($file, (array)$v['injectViews']))
			$names[$k] = $v['injectViews'][$file]; //defined order
		elseif (in_array($file, (array)$v['injectViews']))
			$names[$k] = count($names);
	}
	asort($names);
	foreach ($names as $name => $order)
		$template->_subTemplateRender(((string)'['.$name.']'.$file), $template->cache_id, $template->compile_id, 0, $template->cache_lifetime, [], 0, true);

}
