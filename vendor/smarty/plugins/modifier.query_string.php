<?php
/**
 * 加密解密
 * 
 * @param  string $string 输入内容
 * @param  string $mode   encode or decode
 * @return string         输出网址
 */
function smarty_modifier_query_string($key, $value = NULL)
{
	empty($value) && $value = !is_array($key) ? app('request')->input($key) : NULL;
	!is_array($key) && $key = [$key => $value];
	return http_build_query($key);
}