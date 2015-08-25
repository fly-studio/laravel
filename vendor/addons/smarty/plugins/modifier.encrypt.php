<?php
/**
 * 加密解密
 * 
 * @param  string $string 输入内容
 * @param  string $mode   encode or decode
 * @return string         输出加密或解密内容
 */
function smarty_modifier_encrypt($string, $mode = 'encode')
{
	return strtolower($mode) == 'encode' ? Crypt::encrypt($string) : Crypt::decrypt($string);
}