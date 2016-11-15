<?php
/**
 * 支持多语言的basename，原basename无法支持中文等其它语言
 * 	
 * @param  string $param  输入文件名或路径
 * @param  string $suffix 如果文件名是以 suffix 结束的，那这一部分也会被去掉
 * @return string         返回文件名
 */
function mb_basename($param, $suffix = NULL) {
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
	$parts = array();// Array to build a new path from the good parts
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

/**
 * recursively remove a directory
 * 
 * @param  string $dir
 * @param boolean $retain_parent_directory 是否保留父目录
 * @return string
 */
function rmdir_recursive($dir, $retain_parent_directory = FALSE)
{
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file) && !is_link($file))
			rmdir_recursive($file, FALSE);
		else
			unlink($file);
	}
	if (!$retain_parent_directory)
		rmdir($dir);
}

/**
 * 获取文件的扩展名(比如：jpg、php，无句号)
 * @param  string $basename 文件名称或路径
 * @return string           返回不带.的扩展名
 */
function fileext($basename)
{
	return pathinfo($basename, PATHINFO_EXTENSION);
}

if (!function_exists('fnmatch')) { 
	define('FNM_PATHNAME', 1); 
	define('FNM_NOESCAPE', 2); 
	define('FNM_PERIOD', 4); 
	define('FNM_CASEFOLD', 16); 
	
	function fnmatch($pattern, $string, $flags = 0) { 
		return pcre_fnmatch($pattern, $string, $flags); 
	} 
} 

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
		if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) return FALSE; 
	} 
	
	$pattern = '#^' 
		. strtr(preg_quote($pattern, '#'), $transforms) 
		. '$#' 
		. $modifiers; 
	return (boolean)preg_match($pattern, $string); 
}

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

/**
 * 返回某目录下的所有文件，支持过滤
 *
 * @example
 * 忽略列表：可输入绝对路径，或 *\name （表示任意目录的此文件(夹)），可以使用通配符*代表任意路径和文件
 * 如果忽略文件夹，则会忽略本文件夹及子文件，并且请勿以DIRECTORY_SEPARATOR结尾
 * 比如：[APPPATH.'cache', APPPATH.'attachments', APPPATH.'logs', '*'.DIRECTORY_SEPARATOR.'.gitignore', '*'.DIRECTORY_SEPARATOR.'.gitmodules', '*'.DIRECTORY_SEPARATOR.'.git', '*'.DIRECTORY_SEPARATOR.'.svn',]
 * 
 * 注意：fnmatch 的第三个参数如果添加[FNM_PATHNAME]属性，使用[*\filename]过滤[\path\to\filename]，在linux下会返回FALSE
 * 
 * @param  string  $path         需要查找的目录
 * @param  array   $ignore_files 忽略列表，支持通配符，此列表下的所有文件(夹)将被忽略
 * @param  boolean $setting   FILE_LIST_FILE_KEY | FILE_LIST_DEBUG_PATH | FILE_LIST_FILE_INFO | FILE_LIST_FOLDER | FILE_LIST_SUBFOLDER
 * @return array                 返回所有文件(夹)数组
 */
define('FILE_LIST_FILE_KEY', 1); //
define('FILE_LIST_DEBUG_PATH', 3); // 1 | 2 则会隐藏真实路径，将路径替换为APPPATH、SYSPATH、MODPATH
define('FILE_LIST_FILE_INFO', 4);
define('FILE_LIST_INCLUDE_FOLDER', 8);
define('FILE_LIST_SUBFOLDER', 8);

function file_list($path, $include_files = array(), $ignore_files = array(), $setting = NULL)
{
	$result = array();
	$include_files = to_array($include_files);
	$ignore_files = to_array($ignore_files);
	//Format include_files
	foreach ($include_files as $key => $value)
		$include_files[$key] = normalize_path($value);
	//Format ignore_files
	foreach ($ignore_files as $key => $value)
		$ignore_files[$key] = normalize_path($value);
	
	$_setting = array(
		'file_key' => ($setting & FILE_LIST_FILE_KEY) == FILE_LIST_FILE_KEY,
		'debug_path' => ($setting & FILE_LIST_DEBUG_PATH) == FILE_LIST_DEBUG_PATH,
		'file_info' => ($setting & FILE_LIST_FILE_INFO) == FILE_LIST_FILE_INFO,
		'include_folder' => ($setting & FILE_LIST_INCLUDE_FOLDER) == FILE_LIST_INCLUDE_FOLDER,
		'subfolder' => ($setting & FILE_LIST_SUBFOLDER) == FILE_LIST_SUBFOLDER,
	);

	$queue = array($path);$pt = NULL;
	while(list($k, $path) = each($queue))
	{ //3
		///*
		if ($handle = opendir($path)) 
		{
			while (FALSE !== ($file = readdir($handle))) 
			{ //2
				if ($file == '.' || $file == '..') continue 1;
				$real_path =  normalize_path($path.DIRECTORY_SEPARATOR.$file);

				if (is_dir($real_path))
				{
					$_setting['include_folder'] && $queue[] = $real_path;
					if (!$_setting['include_folder']) continue 1;
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
					$pt = &$result[anystring2utf8(str_replace('\\','/', $_setting['debug_path'] ? Debug::path($real_path) : $real_path))];
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
			$result[str_replace('\\','/', $debug_path ? Debug::path($real_path) : $real_path)] = $fileinfo = fileinfo($real_path);
			if ($fileinfo['type'] == 'dir')
				$queue[] = $real_path;
		}
		//*/
	}
	ksort($result);
	//print_r($result);
	return $result;
}
if (!function_exists('sys_get_temp_dir')) {
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
		return NULL;
	}
}


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

/**
 * 字节转化为单位
 * @param  int $size 输入字节数
 * @return string    返回带单位的字节数
 */
function format_bytes($size, $standard_unit = FALSE)
{ 
	$arr = $standard_unit ? array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB') : array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'); 
	$log = floor(log($size, 1024)); 
	return round($size / pow(1024, $log), 2) . ' ' . $arr[$log]; 
}

function create_file($filename, $size = 0)
{
	$dirname = dirname($filename);
	@mkdir($dirname, 0777, true);
	if (disk_free_space($dirname) < $size)
	{
		throw new \Exception('[Warnning] Free space less than '.format_bytes($size));
		return false;
	}
	$fp = fopen($filename, 'wb');
	if ($size > 0)
	{
		fseek($fp, $size - 1, SEEK_CUR); // seek to SIZE-1
		fwrite($fp,"\0");
	}
	fclose($fp);
	return true;
}