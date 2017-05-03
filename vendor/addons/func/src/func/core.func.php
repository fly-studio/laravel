<?php
if( ! function_exists('boolval'))
{
	/** Checks a variable to see if it should be considered a boolean true or false.
	 *     Also takes into account some text-based representations of true of false,
	 *     such as 'false','N','yes','on','off', etc.
	 * @author Samuel Levy <sam+nospam@samuellevy.com>
	 * @param mixed $in The variable to check
	 * @param bool $strict If set to false, consider everything that is not false to
	 *                     be true.
	 * @return bool The boolean equivalent or null
	 */
	function boolval($in, $strict = FALSE) 
	{
		$out = NULL;
		// if not strict, we only have to check if something is false
		$false_array = array('false', 'False', 'FALSE', 'no', 'No', 'n', 'N', '0', 'off', 'Off', 'OFF', FALSE, 0);
		!$strict && $false_array[] = NULL;// 严格模式,NULL返回NULL
		if (in_array($in,  $false_array, TRUE))
		{
			$out = FALSE;
		} else if ($strict) {
			// if strict, check the equivalent true values
			if (in_array($in, array('true', 'True', 'TRUE', 'yes', 'Yes', 'y', 'Y', '1', 'on', 'On', 'ON', TRUE, 1), TRUE))
			{
				$out = TRUE;
			}
		} else {
			// not strict? let the regular php bool check figure it out (will
			//     largely default to true)
			$out = $in ? TRUE : FALSE;
		}
		return $out;
	}
}

function get_type($var) {
	if (is_array($var)) return 'array';
	if (is_bool($var)) return 'boolean';
	if (is_float($var)) return 'float';
	if (is_int($var)) return 'integer';
	if (is_null($var)) return 'NULL';
	if (is_numeric($var)) return 'numeric';
	if (is_object($var)) return 'object';
	if (is_resource($var)) return 'resource';
	if (is_string($var)) return 'string';
	return FALSE;
}
/**
 * 交换两个数字，如果传入字符串，会截断长的那个字符串
 * @param  number $a 数字1
 * @param  number $b 数字2
 */
function swap(&$a, &$b)
{
	$a ^= $b ^= $a ^= $b; //根据C99标准，这种写法其实是undefined behavior
	//$a ^= $b; $b ^= $a; $a ^= $b;
}

/**
 * 反转一个32位变量(比如int，或1个字符)的所有位，比如二进制：00000000,00000000,10000000,10000001反转为10000001,00000001,00000000,00000000
 * @param  mixed $x 
 * @return mixed 
 */
function byterev($x)
{
	$x = (($x >>  1) & 0x55555555) | (($x <<  1) & 0xaaaaaaaa) ;
	$x = (($x >>  2) & 0x33333333) | (($x <<  2) & 0xcccccccc) ;
	$x = (($x >>  4) & 0x0f0f0f0f) | (($x <<  4) & 0xf0f0f0f0) ;
	$x = (($x >>  8) & 0x00ff00ff) | (($x <<  8) & 0xff00ff00) ;
	$x = (($x >> 16) & 0x0000ffff) | (($x << 16) & 0xffff0000) ;
	return $x;
}

/**
 * 计算出一个32位变量的1的个数，比如：十进制 124 的二进制为 1111100，故1的个数为5
 * @param  mixed $x 
 * @return int
 */
function byte_pop($x)
{
	$x = ($x & 0x55555555) + (($x & 0xaaaaaaaa) >> 1);
	$x = ($x & 0x33333333) + (($x & 0xcccccccc) >> 2);
	$x = ($x & 0x0f0f0f0f) + (($x & 0xf0f0f0f0) >> 4);
	$x = ($x & 0x00ff00ff) + (($x & 0xff00ff00) >> 8);
	$x = ($x & 0x0000ffff) + (($x & 0xffff0000) >> 16);

	//更好的写法是
	/*
	$x = $x - (($x>>1) & 0x55555555);
	$x = ($x & 0x33333333) + (($x >> 2) & 0x33333333);
	$x = ($x + ($x >> 4)) & 0x0f0f0f0f;
	$x += $x >> 8;
	$x += $x >> 16;
	*/
	return $x;
}
/**
 * 是否是内网IP
 * ip地址中预留的内网ip地址如下：
 * A类： 10.0.0.0 ～ 10.255.255.255
 * B类： 172.16.0.0 ～ 172.31.255.255
 * C类： 192.168.0.0 ～ 192.168.255.255
 * 二进制表示：
 * A类： 00001010 00000000 00000000 00000000 ～ 00001010 11111111 11111111 11111111 
 * B类： 10101100 00010000 00000000 00000000 ～ 10101100 00011111 11111111 11111111
 * C类： 11000000 10101000 00000000 00000000 ～ 11000000 10101000 11111111 11111111
 * 
 * @param  string  $ip 输入ipv4的ip格式
 * @return boolean     是否为内网IP
 */
function is_internal_ip($ip) { 
	$ip = ip2long($ip); 
	$net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址 
	$net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址 
	$net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
	return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c; 
}

/**  
* 检查ip是否属于某个子网 
* 
* @param subnet:子网;如: 10.10.10/24 或 10.10.10.0/24 都是一样的 
* @return true | false 
*/ 
function ip_in_subnet($subnet, $ip) 
{
	$arr = explode('/',trim($subnet)); 
	if(count($arr) == 1) //支持单个ip的写法 
		return $ip == $arr[0]; 

	$long_ip = ip2long($ip); 
	$net = ip2long($arr[0]); 
	$hosts = pow(2, 32 - $arr[1]) - 1; //主机部分最大值 
	$host = $net ^ $long_ip; //客户端ip的主机部分
	return $host >= 0 && $host <= $hosts; 
}
/**
 * 将IP字符串转换为无符号长整型
 *
 * @param string $ip IP字符串
 * @return integer 无符号长整形IP
 */
function ip2ulong($ip){
	if (empty($ip) || $ip == 'unknown') return 0;
	$i = 0;
	if (function_exists('ip2long')) 
		$i = ip2long($ip);
	else {
		list($tmp1,$tmp2,$tmp3,$tmp4) = explode('.',$ip);
		$tmp1 = intval($tmp1);
		$tmp2 = intval($tmp2);
		$tmp3 = intval($tmp3);
		$tmp4 = intval($tmp4);
		if ($tmp1 > 255 || $tmp2 > 255 || $tmp3 > 255 || $tmp4 > 255 || $tmp1 < 0 || $tmp2 < 0 || $tmp3 < 0 || $tmp4 < 0) {
			return 0;
		}
		$i = hexdec(str_pad(dechex($tmp1),2,STR_PAD_LEFT) . str_pad(dechex($tmp2),2,STR_PAD_LEFT) . str_pad(dechex($tmp3),2,STR_PAD_LEFT) . str_pad(dechex($tmp4),2,STR_PAD_LEFT));
	}
	return sprintf("%u", $i);
}
/**
 * 将IP无符号长整型转换成字符串
 * 
 * @param integer $ip 无符号长整形字符串
 * @return string IP字符串
 */
function ulong2ip($ip){
	if (empty($ip)) return 'unknown';
	$ip = -(4294967296 - $ip);
	$s = '0.0.0.0';
	if (function_exists('long2ip')) 
		$s = long2ip($ip);
	else {
		$tmp[1] = $ip & 0xFF;
		$tmp1 = $ip >> 8;
		$tmp[2] = $tmp1 & 0xFF;
		$tmp1 = $ip >> 16;
		$tmp[3] = $tmp1 & 0xFF;
		$tmp1 = $ip >> 24;
		$tmp[4] = $tmp1 & 0xFF;
		krsort($tmp);
		$s = implode('.',$tmp);
	}
	return $s;
}

/**
 *	Program to perform ip whois
 *	Silver Moon
 *	m00n.silv3r@gmail.com
 *
 *	Get the whois content of an ip by selecting the correct server
 */
function get_whois($ip){
	$w = get_whois_from_server('whois.iana.org' , $ip);
	preg_match('@whois\.[\w\.]*@si' , $w , $data);
	$whois_server = $data[0];
	//echo $whois_server;
	//now get actual whois data
	$whois_data = get_whois_from_server($whois_server , $ip);
	return $whois_data;
}

/**
 *	Get the whois result from a whois server
 *	return text
 */
function get_whois_from_server($server , $ip)
{
	$data = '';
	#Before connecting lets check whether server alive or not
	#Create the socket and connect
	$f = fsockopen($server, 43, $errno, $errstr, 3);	//Open a new connection
	if(!$f)	
		return NULL;
	#Set the timeout limit for read
	stream_set_timeout($f , 3) || die('Unable to set set_timeout');	#Did this solve the problem ?
	#Send the IP to the whois server
	$f && fputs($f, "$ip\r\n");
	/*Theory : stream_set_timeout must be set after a write and before a read for it to take effect on the read operation
		If it is set before the write then it will have no effect : http://in.php.net/stream_set_timeout*/
	//Set the timeout limit for read
	stream_set_timeout($f , 3) || die('Unable to stream_set_timeout');	#Did this solve the problem ?
	//Set socket in non-blocking mode
	stream_set_blocking ($f, 0 );
	//If connection still valid
	if ($f)
		while (!feof($f))
			$data .= fread($f , 128);
	//Now return the data
	return $data;
}

/**
 * 函数:获取当前访问者的IP(字符串)
 *
 * @return string 
 */
function get_client_ip($strict = FALSE)
{
	if ($strict)
	{
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
	{
		$onlineip = getenv('HTTP_CLIENT_IP');
	}
	else if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
	{
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	}
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown'))
	{
		$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
	{
		$onlineip = getenv('REMOTE_ADDR');
	}
	else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
	{
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	unset($onlineipmatches);//释放
	return $onlineip;
}

/**
 * 获取当前的页面万维网地址
 *
 * @param boolean $
 * @return string 按条件返回url
 */

define('HTTP_URL_SCHEME', 1);
define('HTTP_URL_PATH', 2);
define('HTTP_URL_SCRIPT', 4);
define('HTTP_URL_PATHINFO', 8);
define('HTTP_URL_QUERY', 16);
define('HTTP_URL_ALL', HTTP_URL_SCHEME | HTTP_URL_PATH | HTTP_URL_SCRIPT | HTTP_URL_PATHINFO | HTTP_URL_QUERY);
function get_current_url($flags = HTTP_URL_ALL )
{
	$url = $_SERVER['SERVER_NAME']. ((!empty($_SERVER["HTTPS"]) && $_SERVER["SERVER_PORT"] == 443) || (empty($_SERVER["HTTPS"]) && $_SERVER["SERVER_PORT"] == 80) ? '' : ':'.$_SERVER["SERVER_PORT"]);
	($flags & HTTP_URL_SCHEME) > 0 && $url = ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'].':' : '').'//'.$url;
	($flags & HTTP_URL_PATH) > 0 && $url .= preg_replace('#[/\\\\]+#','/',dirname($_SERVER['SCRIPT_NAME']).'/') ;
	($flags & HTTP_URL_SCRIPT) > 0 && $url .= basename($_SERVER['SCRIPT_NAME']);
	($flags & HTTP_URL_PATHINFO) > 0 && $url .= empty($_SERVER['PATH_INFO']) ? '' : $_SERVER["PATH_INFO"];
	($flags & HTTP_URL_QUERY) > 0 && $url .= empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING'];
	return $url;
}

if (!function_exists('get_namespace'))
{
	function get_namespace($class)
	{
		$class_name = is_object($class) ? get_class($class) : $class;
		return substr($class_name, 0, strrpos($class_name, '\\'));
	}
}

/**
 * set class's public/private/protected property
 *
 * @param object $class
 * @param string $variant property name
 * @param string $value value
 *
 * @return array
 */
if (!function_exists('set_property'))
{
function set_property($class, $variant, $value)
{
	if (!is_object($class)) throw new Exception('paramater #0 must be an object\'s instance.', 1);

    $property = (new ReflectionClass($class))->getProperty($variant);
    $property->setAccessible(true);

    return $property->setValue($class, $value);
}
}
/**
 * get class's public/private/protected property
 *
 * @param object $class
 * @param string $variant property name
 *
 * @return array
 */
if (!function_exists('get_property'))
{
function get_property($class, $variant)
{
	if (!is_object($class)) throw new Exception('paramater #0 must be an object\'s instance.', 1);
	
    $property = (new ReflectionClass($class))->getProperty($variant);
    $property->setAccessible(true);

    return $property->getValue($class);
}
}

/**
 * call class's public/private/protected method
 *
 * @param object $class
 * @param string $variant property name
 * @param string $value value
 *
 * @return array
 */
if (!function_exists('call_class_method_array'))
{
function call_class_method_array($class, $method, $parameters)
{
	if (!is_object($class)) throw new Exception('paramater #0 must be an object\'s instance.', 1);

    $reflectionMethod = (new ReflectionClass($class))->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($class, $parameters);
}
}

/**
 * call class's public/private/protected method
 *
 * @param object $class
 * @param string $variant property name
 * @param string $value value
 *
 * @return array
 */
if (!function_exists('call_class_method'))
{
function call_class_method($class, $method, ...$parameters)
{
	if (!is_object($class)) throw new Exception('paramater #0 must be an object\'s instance.', 1);

    $reflectionMethod = (new ReflectionClass($class))->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($class, $parameters);
}
}

if (!function_exists('base64_urlencode'))
{
	function base64_urlencode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}

if (!function_exists('base64_urldecode'))
{
	function base64_urldecode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
}