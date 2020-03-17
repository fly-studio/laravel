<?php

if (! function_exists('mb_basename')) {
/**
 * 支持多语言的basename，原basename无法支持中文等其它语言
 *
 * @param  string $param  输入文件名或路径
 * @param  string $suffix 如果文件名是以 suffix 结束的，那这一部分也会被去掉
 * @return string         返回文件名
 */
function mb_basename($param, $suffix = null) {
	$param = str_replace('\\', '/', $param);
	if ( $suffix ) {
		$tmpstr = ltrim(substr($param, strrpos($param, '/') ), '/');
		if ( (strpos($param, $suffix) + strlen($suffix) )  ==  strlen($param) ) {
			return str_ireplace( $suffix, '', $tmpstr);
		} else {
			return ltrim(substr($param, strrpos($param, '/') ), '/');
		}
	} else {
		return ltrim(substr($param, strrpos($param, '/') ), '/');
	}
}
}

if (! function_exists('relative_path')) {
/**
 * 根据base_path，取出target_path相对路径
 * @example
 * input: relative_path('/var/www/home/1.php', '/var/www/')
 * output: ./home/1.php
 *
 * @param  string $target_path 绝对路径
 * @param  string $base_path   根目录路径
 * @return string              输出相对路径
 */
function relative_path($target_path, $base_path = __FILE__ )
{
	// some compatibility fixes for Windows paths
	$base_path = is_dir($base_path) ? rtrim($base_path, '\/') . '/' : $base_path;
	$target_path = is_dir($target_path) ? rtrim($target_path, '\/') . '/'   : $target_path;
	$base_path = str_replace('\\', '/', $base_path);
	$target_path = str_replace('\\', '/', $target_path);

	$base_path = explode('/', $base_path);
	$target_path = explode('/', $target_path);
	$relPath  = $target_path;

	foreach($base_path as $depth => $dir) {
		// find first non-matching dir
		if($dir === $target_path[$depth]) {
			// ignore this directory
			array_shift($relPath);
		} else {
			// get number of remaining dirs to $base_path
			$remaining = count($base_path) - $depth;
			if($remaining > 1) {
				// add traversals up to first matching dir
				$padLength = (count($relPath) + $remaining - 1) * -1;
				$relPath = array_pad($relPath, $padLength, '..');
				break;
			} else {
				$relPath[0] = './' . $relPath[0];
			}
		}
	}
	return implode('/', $relPath);
}
}

if (! function_exists('normalize_path')) {
/**
 * 去掉路径中多余的..或/
 * @example
 * Will convert /path/to/test/.././..//..///..///../one/two/../three/filename
 * to ../../one/three/filename
 *
 * @param  string $path 输入路径
 * @return string       输出格式化之后的路径
 */
function normalize_path($path, $separator = DIRECTORY_SEPARATOR)
{
	$parts = [];// Array to build a new path from the good parts
	$path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
	$path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
	$segments = explode('/', $path);// Collect path segments
	$test = '';// Initialize testing variable
	foreach($segments as $segment)
	{
		if($segment != '.')
		{
			$test = array_pop($parts);
			if(is_null($test))
				$parts[] = $segment;
			else if($segment == '..')
			{
				if($test == '..')
					$parts[] = $test;

				if($test == '..' || $test == '')
					$parts[] = $segment;
			}
			else
			{
				$parts[] = $test;
				$parts[] = $segment;
			}
		}
	}
	return implode($separator, $parts);
}
}

if (! function_exists('rmdir_recursive')) {
/**
 * recursively remove a directory
 *
 * @param  string $dir
 * @param boolean $retain_parent_directory 是否保留父目录
 * @return string
 */
function rmdir_recursive($dir, $retain_parent_directory = false)
{
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file) && !is_link($file))
			rmdir_recursive($file, false);
		else
			unlink($file);
	}
	if (!$retain_parent_directory)
		rmdir($dir);
}
}

if (! function_exists('fileext')) {
/**
 * 获取文件的扩展名(比如：jpg、php，无句号)
 * @param  string $basename 文件名称或路径
 * @return string           返回不带.的扩展名
 */
function fileext($basename)
{
	return pathinfo($basename, PATHINFO_EXTENSION);
}
}

if (! function_exists('fnmatch')) {
define('FNM_PATHNAME', 1);
define('FNM_NOESCAPE', 2);
define('FNM_PERIOD', 4);
define('FNM_CASEFOLD', 16);

function fnmatch($pattern, $string, $flags = 0) {
	return pcre_fnmatch($pattern, $string, $flags);
}
}

if (! function_exists('pcre_fnmatch')) {
/**
 * 同fnmatch函数，用于支持低版本的PHP
 *
 * @param  string  $pattern 支持通配符的表达式
 * @param  string  $string  需要匹配的字符串
 * @param  integer $flags   参数：FNM_PATHNAME FNM_NOESCAPE FNM_PERIOD FNM_CASEFOLD
 * @return boolean          是否匹配
 */
function pcre_fnmatch($pattern, $string, $flags = 0) {
	//return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
	$modifiers = null;
	$transforms = array(
		'\*'    => '.*',
		'\?'    => '.',
		'\[\!'    => '[^',
		'\['    => '[',
		'\]'    => ']',
		'\.'    => '\.',
		'\\'    => '\\\\'
	);

	// Forward slash in string must be in pattern:
	if ($flags & FNM_PATHNAME) {
		$transforms['\*'] = '[^/]*';
	}

	// Back slash should not be escaped:
	if ($flags & FNM_NOESCAPE) {
		unset($transforms['\\']);
	}

	// Perform case insensitive match:
	if ($flags & FNM_CASEFOLD) {
		$modifiers .= 'i';
	}

	// Period at start must be the same as pattern:
	if ($flags & FNM_PERIOD) {
		if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) return false;
	}

	$pattern = '#^'
		. strtr(preg_quote($pattern, '#'), $transforms)
		. '$#'
		. $modifiers;
	return (boolean)preg_match($pattern, $string);
}
}

if (! function_exists('fileinfo')) {
/**
 * 返回文件的一些基本属性
 *
 * @param  string $path 文件路径
 * @return array        返回属性数组
 */
function fileinfo($path)
{
	$stat = /*array(
		'uid' => fileowner($path),
		'gid' => filegroup($path),
		'size' => filesize($path),
		'mtime' => filemtime($path),
		'atime' => fileatime($path),
		'ctime' => filectime($path),
	);*/stat($path);
	return array(
		'type' => is_dir($path) ? 'dir' : (is_file($path) ? 'file' : 'other') /*filetype($path) 比较耗费资源*/,
		'path' => $path,
		'uid' => $stat['uid'],
		'gid' => $stat['gid'],
		'size' => $stat['size'],
		'mtime' => $stat['mtime'],
		'atime' => $stat['atime'],
		'atime' => $stat['atime'],
		'ctime' => $stat['ctime'],
		'nlink' => $stat['nlink'],
		//'readable' => is_readable($path), /*比较耗费资源*/
		//'writable' => is_writable($path), /*比较耗费资源*/
	);
}
}

if (! function_exists('file_list')) {
/**
 * 返回某目录下的所有文件，支持过滤
 *
 * @example
 * 忽略列表：可输入绝对路径，或 *\name （表示任意目录的此文件(夹)），可以使用通配符*代表任意路径和文件
 * 如果忽略文件夹，则会忽略本文件夹及子文件，并且请勿以DIRECTORY_SEPARATOR结尾
 * 比如：[base_path('cache'), base_path('attachments'), base_path('logs'), '*'.DIRECTORY_SEPARATOR.'.gitignore', '*'.DIRECTORY_SEPARATOR.'.gitmodules', '*'.DIRECTORY_SEPARATOR.'.git', '*'.DIRECTORY_SEPARATOR.'.svn',]
 *
 * 注意：fnmatch 的第三个参数如果添加[FNM_PATHNAME]属性，使用[*\filename]过滤[\path\to\filename]，在linux下会返回false
 *
 * @param  string  $path         需要查找的目录
 * @param  array   $ignore_files 忽略列表，支持通配符，此列表下的所有文件(夹)将被忽略
 * @param  boolean $setting   FILE_LIST_FILE_KEY | FILE_LIST_DEBUG_PATH | FILE_LIST_FILE_INFO | FILE_LIST_FOLDER | FILE_LIST_SUBFOLDER
 * @return array                 返回所有文件(夹)数组
 */
define('FILE_LIST_FILE_KEY', 1); // 使用文件路径作为KEY
define('FILE_LIST_HIDDEN_BASE', 3); // 隐藏KEY的真实路径，将路径转化为base_path的相对路径
define('FILE_LIST_FILE_INFO', 4); //返回文件信息
define('FILE_LIST_SUBFOLDER', 8); //去子目录查找

function file_list($path, $include_files = [], $ignore_files = [], $setting = null)
{
	$result = [];
	$include_files = to_array($include_files);
	$ignore_files = to_array($ignore_files);

	//Format include_files
	foreach ($include_files as $key => $value)
		$include_files[$key] = normalize_path($value);

	//Format ignore_files
	foreach ($ignore_files as $key => $value)
		$ignore_files[$key] = normalize_path($value);

	$_setting = [
		'file_key' => ($setting & FILE_LIST_FILE_KEY) == FILE_LIST_FILE_KEY,
		'hidden_base' => ($setting & FILE_LIST_HIDDEN_BASE) == FILE_LIST_HIDDEN_BASE,
		'file_info' => ($setting & FILE_LIST_FILE_INFO) == FILE_LIST_FILE_INFO,
		//'with_folder' => ($setting & FILE_LIST_WITH_FOLDER) == FILE_LIST_WITH_FOLDER,
		'subfolder' => ($setting & FILE_LIST_SUBFOLDER) == FILE_LIST_SUBFOLDER,
	];

	$queue = [$path];
	$pt = null;
	while(list($k, $path) = each($queue))
	{ //3
		///*
		if ($handle = opendir($path))
		{
			while (false !== ($file = readdir($handle)))
			{ //2
				if ($file == '.' || $file == '..') continue 1;
				$real_path =  normalize_path($path.DIRECTORY_SEPARATOR.$file);

				if (is_dir($real_path))
				{
					$_setting['subfolder'] && $queue[] = $real_path;
					if (!$_setting['subfolder']) continue 1;
				}

				foreach ($include_files as $key => $value)
				{ //1
					if (!fnmatch($value, $real_path, FNM_CASEFOLD | FNM_NOESCAPE))
						continue 2;
				}

				foreach($ignore_files as $value)
				{ //1
					if (fnmatch($value, $real_path, FNM_CASEFOLD | FNM_NOESCAPE))
						continue 2;
				}

				if ($_setting['file_key'])
					$pt = &$result[anystring2utf8(str_replace('\\','/', $_setting['hidden_base'] ? str_replace(base_path(), 'BASE_PATH', $real_path) : $real_path))];
				else
					$pt = &$result[];
				$pt = $_setting['file_info'] ? fileinfo($real_path) : $real_path;


			}
		}
		closedir($handle);
		//*/
		/*
		$path .= DIRECTORY_SEPARATOR;
		$list = glob($path.'{.,}*',GLOB_BRACE | GLOB_NOESCAPE );
		foreach ($list as $real_path)
		{
			if ($real_path == $path.'.' || $real_path == $path.'..') continue 1;
			$real_path = normalize_path($real_path);
			foreach($ignore_files as $value)
			{
				if (fnmatch($value, $real_path, FNM_PATHNAME | FNM_CASEFOLD | FNM_NOESCAPE))
					continue 2;
			}
			$result[str_replace('\\','/', $hidden_base ? str_replace(base_path(), 'BASE_PATH', $real_path) : $real_path)] = $fileinfo = fileinfo($real_path);
			if ($fileinfo['type'] == 'dir')
				$queue[] = $real_path;
		}
		//*/
	}
	ksort($result);
	//print_r($result);
	return $result;
}
}

if (! function_exists('sys_get_temp_dir')) {
function sys_get_temp_dir()
{
	if( $temp = getenv('TMP') ) return $temp;
	if( $temp = getenv('TEMP') ) return $temp;
	if( $temp = getenv('TMPDIR') ) return $temp;
	$temp = tempnam(__FILE__, '');
	if (file_exists($temp))
	{
		unlink($temp);
		return dirname($temp);
	}
	return null;
}
}


if (! function_exists('tempnam_sfx')) {
function tempnam_sfx($path, $suffix)
{
	empty($path) && $path = sys_get_temp_dir();
	do
	{
		$file = $path.DIRECTORY_SEPARATOR.mt_rand().$suffix;
		$fp = @fopen($file, 'x');
	}
	while(!$fp);

	fclose($fp);
	return $file;
}
}

if (! function_exists('fileperms_string')) {
function fileperms_string($perms)
{

	if (( $perms  &  0xC000 ) ==  0xC000 ) // Socket
		$info  =  's' ;
	elseif (( $perms  &  0xA000 ) ==  0xA000 ) // Symbolic Link
		$info  =  'l' ;
	elseif (( $perms  &  0x8000 ) ==  0x8000 ) // Regular
		$info  =  '-' ;
	elseif (( $perms  &  0x6000 ) ==  0x6000 ) // Block special
		$info  =  'b' ;
	elseif (( $perms  &  0x4000 ) ==  0x4000 ) // Directory
		$info  =  'd' ;
	elseif (( $perms  &  0x2000 ) ==  0x2000 ) // Character special
		$info  =  'c' ;
	elseif (( $perms  &  0x1000 ) ==  0x1000 ) // FIFO pipe
		$info  =  'p' ;
	else // Unknown
		$info  =  'u' ;

	// Owner
	$info  .= (( $perms  &  0x0100 ) ?  'r'  :  '-' );
	$info  .= (( $perms  &  0x0080 ) ?  'w'  :  '-' );
	$info  .= (( $perms  &  0x0040 ) ?
				(( $perms  &  0x0800 ) ?  's'  :  'x'  ) :
				(( $perms  &  0x0800 ) ?  'S'  :  '-' ));

	// Group
	$info  .= (( $perms  &  0x0020 ) ?  'r'  :  '-' );
	$info  .= (( $perms  &  0x0010 ) ?  'w'  :  '-' );
	$info  .= (( $perms  &  0x0008 ) ?
				(( $perms  &  0x0400 ) ?  's'  :  'x'  ) :
				(( $perms  &  0x0400 ) ?  'S'  :  '-' ));

	// World
	$info  .= (( $perms  &  0x0004 ) ?  'r'  :  '-' );
	$info  .= (( $perms  &  0x0002 ) ?  'w'  :  '-' );
	$info  .= (( $perms  &  0x0001 ) ?
				(( $perms  &  0x0200 ) ?  't'  :  'x'  ) :
				(( $perms  &  0x0200 ) ?  'T'  :  '-' ));

	return $info;
}
}

if (! function_exists('format_bytes')) {
/**
 * 字节转化为单位
 * @param  int $size 输入字节数
 * @return string    返回带单位的字节数
 */
function format_bytes($size, $standard_unit = false)
{
	$arr = $standard_unit ? array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB') : array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
	$log = floor(log($size, 1024));
	return round($size / pow(1024, $log), 2) . ' ' . $arr[$log];
}
}

if (! function_exists('create_file')) {
/**
 * 创建一个空白全是0的文件
 *
 * @param  [type]  $filename 文件名
 * @param  integer $size     大小
 * @return [bool] 正确返回true，失败抛出错误
 */
function create_file($filename, $size = 0)
{
	$dirname = dirname($filename);
	@mkdir($dirname, 0777, true);
	if (disk_free_space($dirname) < $size)
		throw new \Exception('[Warnning] Free space less than '.format_bytes($size));

	$fp = fopen($filename, 'wb');
	if ($size > 0)
	{
		fseek($fp, $size - 1, SEEK_CUR); // seek to SIZE-1
		fwrite($fp,"\0");
	}
	fclose($fp);
	return true;
}
}

if (! function_exists('filepos')) {
/**
 * 在文件中查找指定的字符串，可用于二进制查找
 * 使用strpos做对比，和fopen('r')不同是，\r\n会严格匹配，并不会适配系统
 *
 * @param  string $file   文件路径
 * @param  string $needle 被查找的字符串
 * @param  callable $callback 查找用的函数，默认是strpos
 * @return int/bool       返回offset，没找到返回false
 */
function filepos(string $file, string $needle, callable $callback = null)
{
	$needleLen = strlen($needle);
	$size = intval(1024 * ceil($needleLen / 1024) * 1.5);
	$fp = fopen($file, 'rb');
	$offset = 0;

	$callback = is_callable($callback) ? $callback : function($haystack, $needle) {
		return strpos($haystack, $needle);
	};

	while(!feof($fp)){
		fseek($fp, $offset);
		$data = fread($fp, $size);
		if (strlen($data) <= 0) break;
		if (($i = $callback($data, $needle)) !== false)
		{
			fclose($fp);
			return $offset + $i;
		}

		$offset += strlen($data) - $needleLen + 1;
	}

	fclose($fp);
	return false;
}
}
