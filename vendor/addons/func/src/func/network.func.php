<?php

if(! function_exists('is_internal_ip')) {
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
}

if(! function_exists('ip_in_subnet')) {
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
}

if(! function_exists('ip2ulong')) {
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
}

if(! function_exists('ulong2ip')) {
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
}

if(! function_exists('get_whois')) {
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
}

if(! function_exists('get_whois_from_server')) {
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
}

if(! function_exists('get_client_ip')) {
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
}

if(! function_exists('get_current_url')) {
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
}

if(! function_exists('is_valid_domain')) {
/**
 * validate domain
 *
 * @example [a]                       Y
 * @example [0]                       Y
 * @example [a.b]                     Y
 * @example [localhost]               Y
 * @example [google.com]              Y
 * @example [news.google.co.uk]       Y
 * @example [xn--fsqu00a.xn--0zwm56d] Y
 * @example [goo gle.com]             N
 * @example [google..com]             N
 * @example [google.com ]             N
 * @example [google-.com]             N
 * @example [.google.com]             N
 * @example [<script]                 N
 * @example [alert(]                  N
 * @example [.]                       N
 * @example [..]                      N
 * @example [ ]                       N
 * @example [-]                       N
 * @example []                        N
 *
 * @param  string  $domain
 * @return boolean
 */
function is_valid_domain($domain)
{
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) //valid chars check
            && preg_match("/^.{1,253}$/", $domain) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)   ); //length of each label
}
}
