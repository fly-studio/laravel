<?php

/**
 * 本文件中所有转换函数只支持utf-8、ansi
 * ansi一般指gbk、big5等双字节字符串，在字体设计上，这类字符会处理为双宽度（相比英文）
 * utf-8一律需要处理为non BOM
 * @author Fly Mirage <no email>
 *
 */



/**
 * 按UTF-8分隔为数组，效率比MB_Substr高
 * 0xxxxxxx
 * 110xxxxx 10xxxxxx
 * 1110xxxx 10xxxxxx 10xxxxxx
 * 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
 * 111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
 * 1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
 *
 * @param string $str 输入utf-8字符串
 * @return array 返回成一段数组
 */
function str_split_utf8($str)
{
	return preg_match_all('/./u', removeBOM($str), $out) ? $out[0] : FALSE;
	/*
	// place each character of the string into and array
	$split = 1;
	$array = array(); $len = strlen($str);
	for ( $i = 0; $i < $len; ){
		$value = ord($str[$i]);
		if($value > 0x7F){
			if($value >= 0xC0 && $value <= 0xDF)
				$split = 2;
			elseif($value >= 0xE0 && $value <= 0xEF)
				$split = 3;
			elseif($value >= 0xF0 && $value <= 0xF7)
				$split = 4;
			elseif($value >= 0xF8 && $value <= 0xFB)
				$split = 5;
			elseif($value >= 0xFC)
				$split = 6;

		} else {
			$split = 1;
		}
		$key = '';
		for ( $j = 0; $j < $split; ++$j, ++$i ) {
			$key .= $str[$i];
		}
		$array[] = $key;
	}
	return $array;
	*/
}

/**
 * 按多字节分隔为数组，效率比MB_Substr高
 *
 * @param string $str 输入utf-8字符串
 * @return array 返回成一段数组
 */
function str_split_multibyte($str, $encode = 'GBK')
{
	
	$encode = encoding_aliases($encode);
	$pattern = '/([\x7f-\xff].+?|.)/';//一般是取双字节	
	switch ($encode) {
		//EUC-JP以及扩展
		case 'EUC-JP':  //http://zh.wikipedia.org/wiki/EUC
		case 'EUC-JISX0213':
			$pattern = '/(\x8E[\xA1-\xDF]|\x8F[\xA1-\xFE][\xA1-\xFE]|[\xA1-\xFE][\xA1-\xFE]|.)/';
			break;
		case 'CP51932': //http://ja.wikipedia.org/wiki/EUC-JP
			$pattern = '/([\xA1-\xA8\xAD\xB0-\xF4\xF9-\xFC][\xA1-\xFE]|.)/';
			break;
		case 'eucJP-win'://http://ja.wikipedia.org/wiki/EUC-JP
			$pattern = '/(\x8F[\xA1-\xAB\xB0-\xED\xF3-\xFE][\xA1-\xFE]|[\xF5-\xFE][\xA1-\xFE]|[\xA1-\xA8\xAD\xB0-\xF4][\xA1-\xFE])/';
			break;
		case 'EUC-JP-2004': //http://ja.wikipedia.org/wiki/EUC-JIS-2004
			break;
		//Shift JIS以及扩展
		case 'SJIS': //http://zh.wikipedia.org/wiki/Shift_JIS http://en.wikipedia.org/wiki/Shift_JIS
			// JIS X 0201标准内的半角标点及片假名（0xA1-0xDF），不用特殊处理
			$pattern = '/([\x81-\x9F\xE0-\xEF][\x40-\x7E\x80-\xFC]|.)/';
			break;
		case 'CP932': //http://en.wikipedia.org/wiki/Code_page_932
			//break;
		case 'SJIS-win': //在微软及IBM的日语电脑系统中，在0xFA、0xFB及0xFC的两字节区域，加入了388个JIS X 0208没有收录的符号和汉字。
			$pattern = '/([\x81-\x9F\xE0-\xEF\xFA\xFB\xFC][\x40-\x7E\x80-\xFC]|.)/';
			break;
		
		case 'SJIS-Mobile#DOCOMO':
			break;
		case 'SJIS-Mobile#KDDI':
			break;
		case 'SJIS-Mobile#SOFTBANK':
			break;
		case 'SJIS-mac':
			break;
		case 'SJIS-2004': //http://ja.wikipedia.org/wiki/Shift_JIS-2004 / CJKV Information Processing 2nd edition - P265
			break;
		//JIS以及扩展
		case 'JIS':
			break;
		case 'CP50220': //http://ja.wikipedia.org/wiki/%E6%96%87%E5%AD%97%E3%82%B3%E3%83%BC%E3%83%89
			break;
		case 'CP50220raw':
			break;
		case 'CP50222':
			break;
		case 'ISO-2022-JP': //http://ja.wikipedia.org/wiki/ISO-2022-JP
			break;
		
		case 'CP50222':
			break;
		case 'CP50221':
			break;
		case 'ISO-2022-JP-MS': //http://ja.wikipedia.org/wiki/ISO-2022   ISO-2022-JP-MS : CP50221にユーザー定義文字を追加したもの。ユーザー定義文字はISO/IEC 2022の私用終端バイトで表される。CP932やeucJP-msとの相互変換をユーザー定義文字も含めて保証するために考案されたもので、現実の使用例はほとんど存在しない。
			break;

		case 'JIS-ms':
			break;
		case 'ISO-2022-JP-2004': //http://ja.wikipedia.org/wiki/ISO-2022-JP-2004
			break;
		case 'ISO-2022-JP-MOBILE#KDDI':
			break;
		
		
		//汉字以及扩展
		case 'HZ': //ISO-2022-CN
			break;
		case 'EUC-CN': //GB2312 http://zh.wikipedia.org/wiki/EUC
			$pattern = '/([\xA1-\xF7][\xA1-\xFE]|.)/';
			break;
		case 'CP936': //GBK
			$pattern = '/([\x81-\xFE][\x40-\x7E\x80-\xFE]|.)/';
			break;
		case 'GB18030':
			$pattern = '/([\x81-\xFE][\x40-\x7E\x80-\xFE]|[\x81-\xFE][\x30-\x39][\x81-\xFE][\x30-\x39]|.)/';
			break;
		case 'EUC-TW': //http://zh.wikipedia.org/wiki/EUC
			$pattern = '/(\x8E[\xA1-\xB0][\xA1-\xFE][\xA1-\xFE]|[\xA1-\xFE][\xA1-\xFE]|.)/';
			break;
		case 'BIG-5': //http://zh.wikipedia.org/wiki/%E5%A4%A7%E4%BA%94%E7%A2%BC
			$pattern = '/([\xA1-\xFE][\xA0-\x7E\xA1-\xFE]|.)/';
			break;
		case 'CP950': //http://zh.wikipedia.org/wiki/%E4%BB%A3%E7%A2%BC%E9%A0%81950
			$pattern = '/(\xA3\xE1|\xF9[\xD6-\xFE]|[\xA1-\xFE][\xA0-\x7E\xA1-\xFE]|.)/';
			break;
		//韩语区
		case 'EUC-KR':
			$pattern = '/([\xA1-\xFE][\xA1-\xFE]|.)/';
			break;
		case 'UHC': //CP949 http://en.wikipedia.org/wiki/Code_page_949 / CJKV Information Processing 2nd edition P301
			$pattern = '/([\x84-\xD3][\x41-\x7E\x81-\xFE]|[\xD8-\xDE\xE0-\xF9][\x31-\x7E\x91-\xFE]|.)/';
			break;
		case 'ISO-2022-KR':
			break;
		case 'KOI8-R':
			break;
		case 'KOI8-U':
			break;


		
		
		default:
			return str_split($str);
	}
	return preg_match_all($pattern, $str, $out) ? $out[0] : FALSE;
}

function str_split_unicode($str)
{
	return preg_match_all('/.{2}/', removeBOM($str), $out) ? $out[0] : FALSE;
}

function str_split_utf16($str)
{
	return str_split_unicode($str);
}

function str_split_utf32($str)
{
	return preg_match_all('/.{4}/', removeBOM($str), $out) ? $out[0] : FALSE;
}

function str_split_any($str, $encode = NULL)
{
	empty($encode) && $encode = mb_detect_encoding($str, 'ASCII,UTF-8,UCS-2,UCS-4,UTF-16,UTF-32');
	$result = array();

	$encode = encoding_aliases($encode);
	switch ($encode) {
		case 'UTF-8':
		case 'UTF-8-Mobile#DOCOMO':
		case 'UTF-8-Mobile#KDDI-A':
		case 'UTF-8-Mobile#KDDI-B':
		case 'UTF-8-Mobile#SOFTBANK':
			$result = str_split_utf8($str);
			break;
		case 'UTF-16':
		case 'UTF-16LE':
		case 'UTF-16BE':
		case 'UCS-2':
		case 'UCS-2LE':
		case 'UCS-2BE':
			$result = str_split_utf16($str);
			break;
		case 'UTF-32':
		case 'UTF-32LE':
		case 'UTF-32BE':
		case 'UCS-4':
		case 'UCS-4LE':
		case 'UCS-4BE':
			$result = str_split_utf32($str);
			break;
		case 'ASCII': //单字节
		case '8bit':
		//拉丁文区
		case 'ISO-8859-1':
		case 'ISO-8859-2':
		case 'ISO-8859-3':
		case 'ISO-8859-4':
		case 'ISO-8859-5':
		case 'ISO-8859-6':
		case 'ISO-8859-7':
		case 'ISO-8859-8':
		case 'ISO-8859-9':
		case 'ISO-8859-10':
		case 'ISO-8859-13':
		case 'ISO-8859-14':
		case 'ISO-8859-15':
		case 'ISO-8859-16':
		case 'Windows-1251':
		case 'Windows-1252':
		case 'Windows-1254':

		case 'CP850':
		case 'CP866':
		case 'ArmSCII-8':

		//单字节区
		case 'pass':
		case 'auto':
		case 'wchar':
		case 'byte2be':
		case 'byte2le':
		case 'byte4be':
		case 'byte4le':
		case 'BASE64':
		case 'UUENCODE':
		case 'HTML-ENTITIES':
		case 'Quoted-Printable':
		case '7bit':
			$result = str_split($str);
			break;
		default:
			$result = str_split_multibyte($str, $encode);
			break;
	}
	return $result;
}

function encoding_aliases($encode)
{
	$_encode = '';
	switch (strtolower($encode)) {
		case 'pass': //pass
		case 'none': //none
			$_encode = 'pass';
			break;
		case 'auto': //auto
		case 'unknown': //unknown
			$_encode = 'auto';
			break;
		case 'wchar': //wchar
			$_encode = 'wchar';
			break;
		case 'byte2be': //byte2be
			$_encode = 'byte2be';
			break;
		case 'byte2le': //byte2le
			$_encode = 'byte2le';
			break;
		case 'byte4be': //byte4be
			$_encode = 'byte4be';
			break;
		case 'byte4le': //byte4le
			$_encode = 'byte4le';
			break;
		case 'base64': //BASE64
			$_encode = 'BASE64';
			break;
		case 'uuencode': //UUENCODE
			$_encode = 'UUENCODE';
			break;
		case 'html-entities': //HTML-ENTITIES
		case 'html': //HTML
		case 'html': //html
			$_encode = 'HTML-ENTITIES';
			break;
		case 'quoted-printable': //Quoted-Printable
		case 'qprint': //qprint
			$_encode = 'Quoted-Printable';
			break;
		case '7bit': //7bit
			$_encode = '7bit';
			break;
		case '8bit': //8bit
		case 'binary': //binary
			$_encode = '8bit';
			break;
		case 'ucs-4': //UCS-4
		case 'iso-10646-ucs-4': //ISO-10646-UCS-4
		case 'ucs4': //UCS4
			$_encode = 'UCS-4';
			break;
		case 'ucs-4be': //UCS-4BE
		case 'ucs4be': //UCS-4BE
			$_encode = 'UCS-4BE';
			break;
		case 'ucs-4le': //UCS-4LE
		case 'ucs4le': //UCS-4LE
			$_encode = 'UCS-4LE';
			break;
		case 'ucs-2': //UCS-2
		case 'iso-10646-ucs-2': //ISO-10646-UCS-2
		case 'ucs2': //UCS2
		case 'unicode': //UNICODE
			$_encode = 'UCS-2';
			break;
		case 'ucs-2be': //UCS-2BE
		case 'ucs2be': //UCS-2BE
			$_encode = 'UCS-2BE';
			break;
		case 'ucs-2le': //UCS-2LE
		case 'ucs2le': //UCS-2LE
			$_encode = 'UCS-2LE';
			break;
		case 'utf-32': //UTF-32
		case 'utf32': //utf32
			$_encode = 'UTF-32';
			break;
		case 'utf-32be': //UTF-32BE
		case 'utf32be': //UTF-32BE
			$_encode = 'UTF-32BE';
			break;
		case 'utf-32le': //UTF-32LE
		case 'utf32le': //UTF-32LE
			$_encode = 'UTF-32LE';
			break;
		case 'utf-16': //UTF-16
		case 'utf16': //utf16
			$_encode = 'UTF-16';
			break;
		case 'utf-16be': //UTF-16BE
		case 'utf16be': //UTF-16BE
			$_encode = 'UTF-16BE';
			break;
		case 'utf-16le': //UTF-16LE
		case 'utf16le': //UTF-16LE
			$_encode = 'UTF-16LE';
			break;
		case 'utf-8': //UTF-8
		case 'utf8': //utf8
			$_encode = 'UTF-8';
			break;
		case 'utf-7': //UTF-7
		case 'utf7': //utf7
			$_encode = 'UTF-7';
			break;
		case 'utf7-imap': //UTF7-IMAP
			$_encode = 'UTF7-IMAP';
			break;
		case 'ascii': //ASCII
		case 'ansi_x3.4-1968': //ANSI_X3.4-1968
		case 'iso-ir-6': //iso-ir-6
		case 'ansi_x3.4-1986': //ANSI_X3.4-1986
		case 'iso_646.irv:1991': //ISO_646.irv:1991
		case 'us-ascii': //US-ASCII
		case 'iso646-us': //ISO646-US
		case 'us': //us
		case 'ibm367': //IBM367
		case 'ibm-367': //IBM-367
		case 'cp367': //cp367
		case 'csascii': //csASCII
			$_encode = 'ASCII';
			break;
		case 'euc-jp': //EUC-JP
		case 'euc': //EUC
		case 'euc_jp': //EUC_JP
		case 'eucjp': //eucJP
		case 'x-euc-jp': //x-euc-jp
			$_encode = 'EUC-JP';
			break;
		case 'sjis': //SJIS
		case 'x-sjis': //x-sjis
		case 'shift-jis': //SHIFT-JIS
			$_encode = 'SJIS';
			break;
		case 'eucjp-win': //eucJP-win
		case 'eucjp-open': //eucJP-open
		case 'eucjp-ms': //eucJP-ms
			$_encode = 'eucJP-win';
			break;
		case 'euc-jp-2004': //EUC-JP-2004
		case 'euc_jp-2004': //EUC_JP-2004
			$_encode = 'EUC-JP-2004';
			break;
		case 'sjis-win': //SJIS-win
		case 'sjis-open': //SJIS-open
		case 'sjis-ms': //SJIS-ms
			$_encode = 'SJIS-win';
			break;
		case 'sjis-mobile#docomo': //SJIS-Mobile#DOCOMO
		case 'sjis-docomo': //SJIS-DOCOMO
		case 'shift_jis-imode': //shift_jis-imode
		case 'x-sjis-emoji-docomo': //x-sjis-emoji-docomo
			$_encode = 'SJIS-Mobile#DOCOMO';
			break;
		case 'sjis-mobile#kddi': //SJIS-Mobile#KDDI
		case 'sjis-kddi': //SJIS-KDDI
		case 'shift_jis-kddi': //shift_jis-kddi
		case 'x-sjis-emoji-kddi': //x-sjis-emoji-kddi
			$_encode = 'SJIS-Mobile#KDDI';
			break;
		case 'sjis-mobile#softbank': //SJIS-Mobile#SOFTBANK
		case 'sjis-softbank': //SJIS-SOFTBANK
		case 'shift_jis-softbank': //shift_jis-softbank
		case 'x-sjis-emoji-softbank': //x-sjis-emoji-softbank
			$_encode = 'SJIS-Mobile#SOFTBANK';
			break;
		case 'sjis-mac': //SJIS-mac
		case 'macjapanese': //MacJapanese
		case 'x-mac-japanese': //x-Mac-Japanese
			$_encode = 'SJIS-mac';
			break;
		case 'sjis-2004': //SJIS-2004
		case 'sjis2004': //SJIS2004
		case 'shift_jis-2004': //Shift_JIS-2004
			$_encode = 'SJIS-2004';
			break;
		case 'utf-8-mobile#docomo': //UTF-8-Mobile#DOCOMO
		case 'utf-8-docomo': //UTF-8-DOCOMO
		case 'utf8-docomo': //UTF8-DOCOMO
			$_encode = 'UTF-8-Mobile#DOCOMO';
			break;
		case 'utf-8-mobile#kddi-a': //UTF-8-Mobile#KDDI-A
			$_encode = 'UTF-8-Mobile#KDDI-A';
			break;
		case 'utf-8-mobile#kddi-b': //UTF-8-Mobile#KDDI-B
		case 'utf-8-mobile#kddi': //UTF-8-Mobile#KDDI
		case 'utf-8-kddi': //UTF-8-KDDI
		case 'utf8-kddi': //UTF8-KDDI
			$_encode = 'UTF-8-Mobile#KDDI-B';
			break;
		case 'utf-8-mobile#softbank': //UTF-8-Mobile#SOFTBANK
		case 'utf-8-softbank': //UTF-8-SOFTBANK
		case 'utf8-softbank': //UTF8-SOFTBANK
			$_encode = 'UTF-8-Mobile#SOFTBANK';
			break;
		case 'cp932': //CP932
		case 'ms932': //MS932
		case 'windows-31j': //Windows-31J
		case 'ms_kanji': //MS_Kanji
			$_encode = 'CP932';
			break;
		case 'cp51932': //CP51932
		case 'cp51932': //cp51932
			$_encode = 'CP51932';
			break;
		case 'jis': //JIS
			$_encode = 'JIS';
			break;
		case 'iso-2022-jp': //ISO-2022-JP
			$_encode = 'ISO-2022-JP';
			break;
		case 'iso-2022-jp-ms': //ISO-2022-JP-MS
		case 'iso2022jpms': //ISO2022JPMS
			$_encode = 'ISO-2022-JP-MS';
			break;
		case 'gb18030': //GB18030
		case 'gb-18030': //gb-18030
		case 'gb-18030-2000': //gb-18030-2000
			$_encode = 'GB18030';
			break;
		case 'windows-1252': //Windows-1252
		case 'cp1252': //cp1252
			$_encode = 'Windows-1252';
			break;
		case 'windows-1254': //Windows-1254
		case 'cp1254': //CP1254
		case 'cp-1254': //CP-1254
		case 'windows-1254': //WINDOWS-1254
			$_encode = 'Windows-1254';
			break;
		case 'iso-8859-1': //ISO-8859-1
		case 'iso_8859-1': //ISO_8859-1
		case 'latin1': //latin1
		case 'latin-1': //latin1
			$_encode = 'ISO-8859-1';
			break;
		case 'iso-8859-2': //ISO-8859-2
		case 'iso_8859-2': //ISO_8859-2
		case 'latin2': //latin2
		case 'latin-2': //latin2
			$_encode = 'ISO-8859-2';
			break;
		case 'iso-8859-3': //ISO-8859-3
		case 'iso_8859-3': //ISO_8859-3
		case 'latin3': //latin3
		case 'latin-3': //latin3
			$_encode = 'ISO-8859-3';
			break;
		case 'iso-8859-4': //ISO-8859-4
		case 'iso_8859-4': //ISO_8859-4
		case 'latin4': //latin4
		case 'latin-4': //latin4
			$_encode = 'ISO-8859-4';
			break;
		case 'iso-8859-5': //ISO-8859-5
		case 'iso_8859-5': //ISO_8859-5
		case 'cyrillic': //cyrillic
			$_encode = 'ISO-8859-5';
			break;
		case 'iso-8859-6': //ISO-8859-6
		case 'iso_8859-6': //ISO_8859-6
		case 'arabic': //arabic
			$_encode = 'ISO-8859-6';
			break;
		case 'iso-8859-7': //ISO-8859-7
		case 'iso_8859-7': //ISO_8859-7
		case 'greek': //greek
			$_encode = 'ISO-8859-7';
			break;
		case 'iso-8859-8': //ISO-8859-8
		case 'iso_8859-8': //ISO_8859-8
		case 'hebrew': //hebrew
			$_encode = 'ISO-8859-8';
			break;
		case 'iso-8859-9': //ISO-8859-9
		case 'iso_8859-9': //ISO_8859-9
		case 'latin5': //latin5
		case 'latin-5': //latin5
			$_encode = 'ISO-8859-9';
			break;
		case 'iso-8859-10': //ISO-8859-10
		case 'iso_8859-10': //ISO_8859-10
		case 'latin6': //latin6
		case 'latin-6': //latin6
			$_encode = 'ISO-8859-10';
			break;
		case 'iso-8859-13': //ISO-8859-13
		case 'iso_8859-13': //ISO_8859-13
			$_encode = 'ISO-8859-13';
			break;
		case 'iso-8859-14': //ISO-8859-14
		case 'iso_8859-14': //ISO_8859-14
		case 'latin8': //latin8
		case 'latin-8': //latin8
			$_encode = 'ISO-8859-14';
			break;
		case 'iso-8859-15': //ISO-8859-15
		case 'iso_8859-15': //ISO_8859-15
			$_encode = 'ISO-8859-15';
			break;
		case 'iso-8859-16': //ISO-8859-16
		case 'iso_8859-16': //ISO_8859-16
			$_encode = 'ISO-8859-16';
			break;
		case 'euc-cn': //EUC-CN
		case 'cn-gb': //CN-GB
		case 'euc_cn': //EUC_CN
		case 'euccn': //eucCN
		case 'x-euc-cn': //x-euc-cn
		case 'gb2312': //gb2312
			$_encode = 'EUC-CN';
			break;
		case 'cp936': //CP936
		case 'cp-936': //CP-936
		case 'gbk': //GBK
			$_encode = 'CP936';
			break;
		case 'iso-2022-cn': //ISO-2022-CN
		case 'iso-2022-cn-ext': //ISO-2022-CN-EXT
		case 'hz': //HZ
			$_encode = 'HZ';
			break;
		case 'euc-tw': //EUC-TW
		case 'euc_tw': //EUC_TW
		case 'euctw': //eucTW
		case 'x-euc-tw': //x-euc-tw
			$_encode = 'EUC-TW';
			break;
		case 'big5': //BIG-5
		case 'big-5': //BIG-5
		case 'cn-big5': //CN-BIG5
		case 'big-five': //BIG-FIVE
		case 'bigfive': //BIGFIVE
			$_encode = 'BIG-5';
			break;
		case 'cp950': //CP950
			$_encode = 'CP950';
			break;
		case 'euc-kr': //EUC-KR
		case 'euc_kr': //EUC_KR
		case 'euckr': //eucKR
		case 'x-euc-kr': //x-euc-kr
			$_encode = 'EUC-KR';
			break;
		case 'uhc': //UHC
		case 'cp949': //CP949
			$_encode = 'UHC'; //http://en.wikipedia.org/wiki/Code_page_949
			break;
		case 'iso-2022-kr': //ISO-2022-KR
			$_encode = 'ISO-2022-KR';
			break;
		case 'windows-1251': //Windows-1251
		case 'cp1251': //CP1251
		case 'cp-1251': //CP-1251
		case 'windows-1251': //WINDOWS-1251
			$_encode = 'Windows-1251';
			break;
		case 'cp866': //CP866
		case 'cp866': //CP866
		case 'cp-866': //CP-866
		case 'ibm866': //IBM866
		case 'ibm-866': //IBM-866
			$_encode = 'CP866'; //http://en.wikipedia.org/wiki/CP866
			break;
		case 'koi8-r': //KOI8-R
		case 'koi8-r': //KOI8-R
		case 'koi8r': //KOI8R
			$_encode = 'KOI8-R';
			break;
		case 'koi8-u': //KOI8-U
		case 'koi8-u': //KOI8-U
		case 'koi8u': //KOI8U
			$_encode = 'KOI8-U';
			break;
		case 'armscii-8': //ArmSCII-8
		case 'armscii-8': //ArmSCII-8
		case 'armscii8': //ArmSCII8
		case 'armscii-8': //ARMSCII-8
		case 'armscii8': //ARMSCII8
			$_encode = 'ArmSCII-8';
			break;
		case 'cp850': //CP850
		case 'cp850': //CP850
		case 'cp-850': //CP-850
		case 'ibm850': //IBM850
		case 'ibm-850': //IBM-850
			$_encode = 'CP850'; //http://en.wikipedia.org/wiki/Code_page_850
			break;
		case 'jis-ms': //JIS-ms
			$_encode = 'JIS-ms';
			break;
		case 'iso-2022-jp-2004': //ISO-2022-JP-2004
			$_encode = 'ISO-2022-JP-2004';
			break;
		case 'iso-2022-jp-mobile#kddi': //ISO-2022-JP-MOBILE#KDDI
		case 'iso-2022-jp-kddi': //ISO-2022-JP-KDDI
			$_encode = 'ISO-2022-JP-MOBILE#KDDI';
			break;
		case 'cp50220': //CP50220
			$_encode = 'CP50220';
			break;
		case 'cp50220raw': //CP50220raw
			$_encode = 'CP50220raw';
			break;
		case 'cp50221': //CP50221
			$_encode = 'CP50221';
			break;
		case 'cp50222': //CP50222
			$_encode = 'CP50222';
			break;
		default:
			$_encode = $encode;
			break;
	}
	return $_encode;
}

/**
 * 按非ascii字符占有几个字宽的方式切分字符串，并且不会将汉字切成半个
 * 所谓字宽是指,使用默认字体显示时，非ascii字符相比英文字符所占大小，比如：宋体、微软雅黑中，汉字占两个宽度
 * @example $ansi_width = 2 表示汉字等非英文字符按照两个字宽长度
 * @example $ansi_width = 1 表示所有字符按一个字宽长度
 *
 * @param string $string 原始字符
 * @param integer $offset 开始偏移,使用方法和substr一样,可以为负数
 * @param integer $length 长度,使用方法和substr一样,可以为负数
 * @param string $charset utf-8 or ansi
 * @param integer $ansi_width 汉字等非英文字符按照几个字符来处理
 * @return string 返回裁减的字符串
 */
function substr_ansi($string, $offset, $length = 0, $charset = 'utf-8', $ansi_width = 1)
{
	if (empty($string)) return FALSE;
	$data = str_split_any($string, $charset);

	$as = $_as = array();
	$_start = $_end = 0;

	foreach($data as $k => $v)
		$as[$k] = strlen($v) > 1 ? $ansi_width : 1;

	$_as_rev = array_reverse($as,true);
	$_as = $offset < 0 ? $_as_rev : $as; 
	$n = 0; $_offset = abs($offset);
	foreach($_as as $k => $v) {
		if ($n >= $_offset) {
			$_start = $k;
			break;
		}
		$n += $v;
	}
	//echo $_start,',';
	$_as = $length <= 0 ? $_as_rev : $as;
	end($_as); list($_end) = each($_as); reset($_as);//给$_end 设定默认值，一直到结尾
	$n = 0; $_length = abs($length);
	foreach($_as as $k => $v) {
		if ($k >= $_start) {
			if ($n >= $_length) {
				$_end = $k + ($length <= 0 ? 1 : 0);
				break;
			}
			$n += $v;
		}
	}
	//echo $_end,'|||||';
	if ($_end <= $_start)
		return '';

	$_data = array_slice($data, $_start, $_end - $_start);

	return implode('',$_data);
}
/**
 * 按非ascii字符占有几个字宽的方式计算字符串长度
 * @example $ansi_width = 2 表示汉字等非英文字符按照两个字宽长度
 * @example $ansi_width = 1 表示所有字符按一个字节长度
 *
 * @param string $string 原始字符
 * @param string $charset utf-8 or ansi
 * @param integer $ansi_width 汉字等非英文字符按照几个字宽来处理
 * @return string 返回字符串长度
 */
function strlen_ansi($string, $charset, $ansi_width = 1)
{
	if (empty($string)) return 0;
	$data = str_split_any($string, $charset);

	$as = 0;
	foreach($data as $k => $v)
		$as += strlen($v) > 1 ? $ansi_width : 1;
	unset($data);
	return $as;
}

/**
 * 裁剪字符串(兼容utf8)
 *
 * @param string $string 输入字符串
 * @param integer $offset 开始裁减的索引(双字节方式)
 * @param integer $length 需要裁减的长度(双字节方式)
 * @param string $charset utf-8 or ansi
 * @param string $dot 裁减之后的字符串末尾补码
 * @return string 输出裁减之后的字符串
 */
function cutstr($string, $offset, $length, $charset = 'UTF-8') //裁剪字符
{

	if(strlen($string) <= $length) {
		return $string;
	}

	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string); //还原html转义符

	$strcut = '';

	$n = $tn = $noc = 0;
	$offset_n = $offset_tn = $offset_noc = 0;
	if(strtolower($charset) == 'utf-8') {
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) { //可见字符
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if ($noc >= $offset && $offset_n == 0) {
				$offset_n = $n;
				$offset_tn = $tn;
				$offset_noc = $noc;
			}

			if($noc >= $offset + $length) {
				break;
			}
		}
	} else {

		while ($n < strlen($string))
		{
			$t = ord($string[$n]);

			if ($t > 127) {
				$tn = 2; $n += 2; $noc += 2;
			} else {
				$tn = 1; $n++; $noc++;
			}
			if ($noc >= $offset && $offset_n == 0) {

				$offset_n = $n;
				$offset_tn = $tn;
				$offset_noc = $noc;
			}
			if($noc >= $offset + $length) {
				break;
			}
		}
	}
	if($offset_noc > $offset) {
		$offset_n -= $offset_tn;
	}
	if($noc > $offset + $length) {
		$n -= $tn;
	}

	$strcut = substr($string, $offset_n, $n - $offset_n);

	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut;
}

/**
 * 移除字符串的BOM
 *
 * @param  string $str 输入字符串
 * @return string 输出字符串
 */
function removeBOM($str)
{
	$str_2 = substr($str, 0, 2);
	$str_3 = substr($str, 0, 3);//$str_2.$str{2};
	$str_4 = substr($str, 0, 4);//$str_3.$str{3};
	if ($str_3 == pack('CCC',0xef,0xbb,0xbf)) //utf-8
		return substr($str, 3);
	elseif ($str_2 == pack('CC',0xfe,0xff) || $str_2 == pack('CC',0xff,0xfe)) //unicode
		return substr($str, 2);
	elseif ($str_4 == pack('CCCC',0x00,0x00,0xfe,0xff) || $str_4 == pack('CCCC',0xff,0xfe,0x00,0x00)) //utf-32
		return substr($str, 4);
	return $str;
}

/**
 * 添加字符串的BOM
 *
 * @param  string $str 输入字符串
 * @return string 输出字符串
 */
function addBOM($str, $encode = 'UTF-8')
{
	$encode = encoding_aliases($encode);
	switch ($encode) {
		case 'UTF-8':
		case 'UTF-8-Mobile#DOCOMO':
		case 'UTF-8-Mobile#KDDI-A':
		case 'UTF-8-Mobile#KDDI-B':
		case 'UTF-8-Mobile#SOFTBANK':
			$bom = pack('CCC',0xef,0xbb,0xbf);
			return substr($str, 0,3) != $bom ? $bom.$str : $str;
		case 'UTF-16':
		case 'UCS-2':
		case 'UCS-2BE':
		case 'UTF-16BE':
			$bom = pack('CC',0xfe,0xff);
			return substr($str, 0,2) != $bom ? $bom.$str : $str;
		case 'UTF-16LE':
		case 'UCS-2LE':
			$bom = pack('CC',0xff,0xfe);
			return substr($str, 0,2) != $bom ? $bom.$str : $str;
		case 'UTF-32':
		case 'UCS-4':
		case 'UTF-32BE':
		case 'UCS-4BE':
			$bom = pack('CCCC',0x00,0x00,0xfe,0xff);
			return substr($str, 0,4) != $bom ? $bom.$str : $str;
		case 'UTF-32LE':
		case 'UCS-4LE':
			$bom = pack('CCCC',0xff,0xfe,0x00,0x00);
			return substr($str, 0,4) != $bom ? $bom.$str : $str;
	}
	return $str;
}

/**
 * 任意编码的字符串转为UTF-8(non BOM)，支持一些常见东方语言
 *
 * @param string $str 输入字符串
 * @return string 输出字符串
 */
function anystring2utf8($str)
{
	$encode = mb_detect_encoding($str,'ASCII,UNICODE,UTF-8,CP936,BIG-5,EUC-TW');
	return removeBOM(!in_array($encode, array('UTF-8','ASCII')) ? iconv($encode,'UTF-8//IGNORE',$str) : $str); //移除BOM的UTF-8
}

/**
 * 任意编码的字符串转为GBK，支持一些常见东方语言
 *
 * @param string $str 输入字符串
 * @return string 输出字符串
 */
function anystring2gbk($str)
{
	$encode = mb_detect_encoding($str,'ASCII,UNICODE,UTF-8,CP936,BIG-5,EUC-TW');
	return (!in_array($encode, array('CP936','ASCII')) ? iconv($encode,'GB18030//TRANSLIT',$str) : $str);
}


/**
 * 任何编码字符串(数组)转换为utf-8
 *
 * @param mixed $string 输入字符串(数组)
 * @return mixed 输出utf-8编码字符串(数组)
 */
function any2utf8($string) //通过递归转换字符串编码
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
		{
			$string[$key] = any2utf8($val); //递归
		}
	}
	else
	{
		$string = anystring2utf8($string);
	}
	return $string;
}

/**
 * 任何编码字符串(数组)转换为gbk
 *
 * @param mixed $string 输入字符串(数组)
 * @return mixed 输出gbk编码字符串(数组)
 */
function any2gbk($string) //通过递归转换字符串编码
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
		{
			$string[$key] = any2gbk($val); //递归
		}
	}
	else
	{
		$string = anystring2gbk($string);
	}
	return $string;
}

/**
 * 获取GUID
 *
 * @return string
 */
function guid()
{
	if (function_exists('com_create_guid'))
	{
		return com_create_guid();
	}
	else
	{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// '-'
		$uuid = chr(123)// '{'
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// '}'
		return $uuid;
	}
}

/**
 * 去掉字符串中的HTML标签
 *
 * @param string $string 输入字符串
 * @return string 返回结果
 */
function nohtml($string)
{
  $string = preg_replace("'<script[^>]*?>.*?</script>'si", "", $string);  //去掉javascript
  $string = preg_replace("'<[\/\!]*?[^<>]*?>'si", "", $string);  //去掉HTML标记 <!DOCTYPE <span> </span>
  $string = preg_replace("@<style[^>]*?>.*?</style>@siU", "", $string);  //去掉style
  $string = preg_replace("@<![\s\S]*?--[ \t\n\r]*>@", "", $string);  //去掉<!--Multi-Line -->
  $string = preg_replace("'([\r\n])[\s]+'", "", $string);  //去掉空白字符
  $string = preg_replace("'&(quot|#34);'i", "", $string);  //替换HTML实体
  $string = preg_replace("'&(amp|#38);'i", "", $string);
  $string = preg_replace("'&(lt|#60);'i", "", $string);
  $string = preg_replace("'&(gt|#62);'i", "", $string);
  $string = preg_replace("'&(nbsp|#160);'i", "", $string);
  return $string;
}

/**
 * 去掉字符串中的Script/Style标签和onload/on...等方法
 *
 * @param string $string 输入字符串
 * @return string 返回结果
 */
function noscript($string)
{
	$string = preg_replace("'<script[^>]*?>.*?</script>'si", "", $string);  //去掉javascript
	$string = preg_replace("'<style[^>]*?>.*?</style>'si", "", $string);  //style
	$string = preg_replace("/<\?/", '', $string); //去掉php
	$string = preg_replace("/\?>/", '', $string);
	$string = preg_replace('#\s*<(/?\w+)\s+(?:on\w+\s*=\s*(["\'\s])?.+?\(\1?.+?\1?\);?\1?)\s*>#is', '<${1}>',$string); //去掉onload/onxxxx
	//$string = preg_replace('#\s*<(/?\w+)\s+(?:on\w+\s*=\s*(["\'\s])?.+?\(\1?.+?\1?\);?\1?|style=["\'].+?["\'])\s*>#is', '<${1}>',$string);

	 // realign javascript href to onclick
	$string = preg_replace("/href=(['\"]).*?javascript:(.*)? \\1/i", "onclick=' $2 '", $string);

	//remove javascript from tags
	while( preg_match("/<(.*)?javascript.*?\(.*?((?>[^()]+) |(?R)).*?\)?\)(.*)?>/i", $string))
		$string = preg_replace("/<(.*)?javascript.*?\(.*?((?>[^()]+)|(?R)).*?\)?\)(.*)?>/i", "<$1$3$4$5>", $string);

	// dump expressions from contibuted content
	if(0) $string = preg_replace("/:expression\(.*?((?>[^(.*?)]+)|(?R)).*?\)\)/i", "", $string);

	while( preg_match("/<(.*)?:expr.*?\(.*?((?>[^()]+)|(?R)).*?\)?\)(.*)?>/i", $string))
		$string = preg_replace("/<(.*)?:expr.*?\(.*?((?>[^()]+)|(?R)).*?\)?\)(.*)?>/i", "<$1$3$4$5>", $string);

	// remove all on* events
	/*
	while( preg_match("/<(.*)?\s?on.+?=?\s?.+?(['\"]).*?\\2 \s?(.*)?>/i", $string) )
		$string = preg_replace("/<(.*)?\s?on.+?=?\s?.+?(['\"]).*?\\2\s?(.*)?>/i", "<$1$3>", $string);
	*/
	$aDisabledAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'ontouchstart', 'ontouchend', 'ontouchmove', 'oninput');
	$string = preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $aDisabledAttributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $string);
	return $string;
}

/**
 * 将数组转换为XML
 * 
 * @param  array $data 输入数组
 * @return string       输出XML字符串
 */
function xml_encode($data, $charset = 'UTF-8')
{
	$xml = '<?xml version="1.0" encoding="'.$charset.'"?><catalog>';
	$a = $data;
	$xml .= _xml_encode($a);
	$xml .= '</catalog>';
	return $xml;
}

function _xml_encode(&$data)
{
	$xml = '';
	foreach($data as $key => $val) {
		is_numeric($key) && $key = "item id=\"$key\"";
		$xml .= '<'.$key;
		$isA = is_array($val);
		!$isA && $xml .= ' type="'.get_type($val).'"';
		$xml .= '>';
		$xml .= $isA ? _xml_encode($val) : htmlspecialchars($val);
		list($key,)= explode(' ',$key);
		$xml .= '</'.$key.'>';
	}
	return $xml;
}

/**
 * 将数组转换为csv(Windows)
 * 
 * @param  array $data 输入数组
 * @param  array $header 标题栏，默认去读取数组的KEY
 * @return string       输出csv字符串
 */
function csv_encode($data, $header = array())
{
	if (empty($data)) return FALSE;

	$_data = array_values($data);
	$_header = empty($header) && is_array($_data[0]) && is_assoc($_data[0]) ? array_keys($_data[0]) : $header;

	$file = fopen('php://temp', 'r+');
	!empty($_header) && fputcsv($file, $_header,',','"');
	foreach ($_data as $value) {
		fputcsv($file, to_array($value),',','"');
	}
	rewind($file);
	$output = stream_get_contents($file);
	fclose($file);

	return addBOM($output);
}
/**
 * 判断字符是否非英文字符/符号 is not a word
 * 
 * @param    $str 输入字符串或字
 * @return boolean      输出是否非英文字符/符号
 */
function isNaW($str) 
{
	return preg_match('/[\\x{7F}-\\x{FF}]/', $str);
	/*for ( $i = 0; $i < strlen($str);++$i ){
		$value = ord($str[$i]); 
		if($value > 127){
			return true;
		}
	}
	return false;*/
}


/**
 * PHP内置的strtr函数
 * 
 * @param  string $str         输入字符串
 * @param  array $replace_arr  需要替换的数组结构
 * @return string              替换之后的字符串
 */
function zend_strtr(&$str, &$replace_arr) {
	$maxlen = 0;$minlen = 1024*128;
	if (empty($replace_arr)) return $str;
	foreach($replace_arr as $k => $v) {
		$len = strlen($k);
		if ($len < 1) continue;
		if ($len > $maxlen) $maxlen = $len;
		if ($len < $minlen) $minlen = $len;
	}
	$len = strlen($str);
	$pos = 0;$result = '';
	while ($pos < $len) {
		if ($pos + $maxlen > $len) $maxlen = $len - $pos;
		$found = false;$key = '';
		for($i = 0;$i<$maxlen;++$i) $key .= $str[$i+$pos];
		for($i = $maxlen;$i >= $minlen;--$i) {
			$key1 = substr($key, 0, $i);
			if (isset($replace_arr[$key1])) {
				$result .= $replace_arr[$key1];
				$pos += $i;
				$found = true;
				break;
			}
		}
		if(!$found) $result .= $str[$pos++];
	}
	return $result;
}

/**
 * 解析dataURL的每一部分为数组
 *
 * @param  string $dataurl 数据
 * @return array
 */
function parse_dataurl($dataurl)
{
	//data:[<mediatype>][;base64],<data>
	//mediatype默认值为text/plain;charset=US-ASCII
	$matches = array();
	preg_match('@^(data:)(?<mime>(.[^/]*/.*?))?(;charset=(?<charset>.[^;]*))?(?<encode>;base64)?,(?<data>.*)$@', $dataurl, $matches);
	$mime = !empty($matches['mime']) ? $matches['mime'] : 'text/plain';
	$charset = $matches['charset'];
	$encode = $matches['encode'];
	$data = !empty($encode) ? base64_decode($matches['data']) : $matches['data'];

	return compact('mime','charset','encode','data');
}

/**
 * Make the place-holder replacements on a line.
 *
 * @param  string  $line
 * @param  array   $replace
 * @return string
 */
if (!function_exists('__'))
{
function __($line, array $replace)
{
	$replace = array_keyflatten($replace, '.', ':');
	ksort($replace);

	return strtr($line, (array)$replace);
}
}