<?php

if (! function_exists('golden_section')) {
/**
 * 黄金分割
 *
 * @return float
 */
function golden_section()
{
	return (sqrt(5) - 1) / 2;
}
}

if (! function_exists('to_excel_serial')) {
/**
 * 像Excel列头一样返回0 => A,1 => B,26 => AA,27 => AB
 *
 * @param  integer $index 输入数字
 * @return string        返回字母
 */
function to_excel_serial(int $index)
{
	$r = 26;
	$str = '';
	if (empty($index)) return 'A';

	while($index > 0)
	{
		if (strlen($str) > 0) --$index;
		$mr = $index % $r;
		$str = chr($mr + 65) . $str;
		$index = floor(($index - $mr) / $r);
	}
	return $str;
}
}

if (! function_exists('from_excel_serial')) {
/**
 * Excel列表号,转化为数字 A => 0 AA =>26 AB => 27
 *
 * @param  string $words 输入字母
 * @return integer        返回数字
 */
function from_excel_serial(string $words)
{
	$words = strtoupper($words);
	if ($words == 'A') return 0;

	$r = 26;
	$index = 0;
	$len = strlen($words);

	for ($i = 0; $i < $len; $i++) {
		$ch = substr($words, $i, 1);
		$index = (ord($ch) - 65 + 1) * pow($r, $len - $i - 1);
	}

	return $index - 1;
}
}

if (! function_exists('is_pow_from_2')) {
/**
 * 是否2的幂，比如 2,4,8,16,32,64,128
 * @return boolean
 */
function is_pow_from_2($x)
{
	return (($x & ($x - 1)) == 0);
}
}

if (! function_exists('variation_and_standard_deviation')) {
/*
平方根倒数速算法
float Q_rsqrt( float number )
{
	long i;
	float x2, y;
	const float threehalfs = 1.5F;

	x2 = number * 0.5F;
	y  = number;
	i  = * ( long * ) &y;                       // evil floating point bit level hacking（对浮点数的邪恶位级hack）
	i  = 0x5f3759df - ( i >> 1 );               // what the fuck?（这他妈的是怎么回事？）
	y  = * ( float * ) &i;
	y  = y * ( threehalfs - ( x2 * y * y ) );   // 1st iteration （第一次牛顿迭代）
//      y  = y * ( threehalfs - ( x2 * y * y ) );   // 2nd iteration, this can be removed（第二次迭代，可以删除）

	return y;
}*/

/**
 * 计算方差
 *
 * @param array $a 数字的数组
 * @return double
 */
function variation_and_standard_deviation(array $a)
{
	if (empty($a))
		return false;
	$average = array_average($a); //计算平均值
	$i = 0.00;
	foreach ($a as $key => $value)
		$i += pow($value + 0 - $average, 2);
	return $i / count($a);
}
}

if (! function_exists('standard_deviation')) {
/**
 * 计算标准差
 *
 * @param array $a 数字的数组
 * @return double
 */
function standard_deviation(array $a)
{
	$i = variation_and_standard_deviation($a);
	return is_numeric($i) ? sqrt($i) : false;
}
}

if (! function_exists('digit_count')) {
/**
 * 计算数字的长度，类似于strlen，但是此函数只计算了整形部分
 *
 * @param  integer  $n    输入数字(整形)
 * @param  integer  $base 进制
 * @return integer       返回长度
 */
function digit_count($n, $base = 10) {
	if($n == 0) return 1;
	// using the built-in log10(x) might be more accurate than log(x)/log(10).
	return $base == 10 ? 1 + floor(log10(abs($n))) : 1 + floor(log(abs($n), $base));
}
}

if (! function_exists('bccomp')) {
/**
 * 对比2个数是否相等
 *
 * 此函数仅仅在bcmath无法使用的情况下可用，
 * 但是无法和bccomp一样支持长整数
 *
 * @param  float  $left_operand  数字1
 * @param  float  $right_operand 数字2
 * @param  integer $scale        精确到小数后面多少位
 * @return integer               0:相等 1:大于 -1:小于
 */
function bccomp(float $left_operand, float $right_operand, int $scale = 0)
{
	$scale < 0 && $scale = 0;
	$digits  = pow(0.1, $scale);
	if (abs($left_operand - $right_operand) < $digits)
		return 0;
	else
		return $left_operand > $right_operand ? 1 : -1;
}
}

if (! function_exists('bclog')) {
/**
 * 大数的LOG
 *
 * @param  string  $x            大数字
 * @param  integer $base         LOG对数
 * @param  integer $decimalplace
 * @return integer               结果
 */
function bclog($x, $base = 10, $decimalplace = 12){
	$integer_value = 0;
	while($x < 1){
		$integer_value = $integer_value - 1 ;
		$x = bcmul($x , $base);
	}
	while($x >= $base){
		$integer_value = $integer_value + 1;
		$x = bcdiv($x , $base );
	}
	$decimal_fraction = 0.0;
	$partial = 1.0;
	# Replace X with X to the 10th power
	$x = bcpow($x , 10);
	while($decimalplace > 0){
		$partial = bcdiv($partial , 10);
		$digit = 0;
		while($x >= $base){
			  $digit = $digit + 1;
			  $x = bcdiv($x , $base);
		}
		$decimal_fraction = bcadd($decimal_fraction , bcmul($digit , $partial));
		# Replace X with X to the 10th power
		$x = bcpow($x , 10);
		$decimalplace = $decimalplace - 1 ;
	}
	return $integer_value + $decimal_fraction ;
}
}

if (! function_exists('bc_rand')) {
/**
 * 大数字的随机数
 *
 * @param  string $min 字符串的数字
 * @param  string $max 字符串的数字
 * @return string      随机数(字符串)
 */
function bc_rand($min, $max)
{
	//x64 最大数字是9223372036854775807
	$difference   = bcadd(bcsub($max, $min), 1);
	$rand_percent = bcdiv(mt_rand(), mt_getrandmax(), 18); // 0 - 1.0
	return bcadd($min, bcmul($difference, $rand_percent, 18), 0);
}
}

if (! function_exists('decimal')) {
/**
 * 取小数部分，可以支持大数
 *
 * @param  float $x
 * @param  integer $precision 保留小数位数
 * @return float            取得的小数部分
 */
function decimal($x, int $precision = NULL)
{
	return bcsub($x ,floor($x), $precision);
}
}

if (! function_exists('average')) {
/**
 * 计算平均数
 *
 * @param array $x
 * @return double
 */
function average($x)
{
	$count = func_num_args();
	if ($count <= 1)
		return !is_array($x) ? $x : array_sum($x) / (empty($x) ? 1 : count($x));

	$args = func_get_args();
	return array_sum($args) / $count;
}
}

if (! function_exists('equilibria')) {
/**
 * 将数组b和a做均衡运算,使b最大值和最小值按一并比例缩小到a的最大值和最小值范围内,最后返回均衡之后的数组
 *
 * @param mixed $a
 * @param mixed $b
 * @param mixed $out 传址进来,返回间距和调整的值
 * @return mixed
 */
function equilibria(array $a, array $b, &$out = NULL)
{
	$out = array('difference' => 0, 'adjust' => 0);
	if (empty($a) || empty($b))
		return false;

	$max = $min = array();
	$max['a'] = max($a);
	$min['a'] = min($a);
	$max['b'] = max($b);
	$min['b'] = min($b);
	if ($max['a'] == 0 && $min['a'] == 0) return $b;
	if ($max['b'] == 0 && $min['b'] == 0) return false;

	decimal_compare($max['a'], $min['a'], 9) == 0 && $max['a'] *= 2;
	decimal_compare($max['b'], $min['b'], 9) == 0 && $max['b'] *= 2;

	$c = array();
	$out['difference'] = ($max['a'] - $min['a']) / ($max['b'] - $min['b']);
	foreach($b as $key => $value)
		$c[$key] = $value * $out['difference'];
	$out['adjust'] = average($c) - average($a); //需要调整的值
	foreach ($c as $key => $value)
		$c[$key] = $value - $out['adjust'];

	return $c;
}
}

if (! function_exists('probability_rand')) {
/**
 * 概率算法
 * @example
 * $list = array(10,50,20,30,10); 默认50会出奖最高
 *
 * @param  array $list
 * @return mixed         返回KEY
 */
function probability_rand(array $list) {
	$result = false;
	//概率数组的总概率精度
	$sum = array_sum($list);
	arsort($list);
	//概率数组循环
	foreach ($list as $k => $v) {
		$randNum = mt_rand(1, $sum);
		if ($randNum <= $v) {
			$result = $k;
			break;
		} else {
			$sum -= $v;
		}
	}
	unset ($list);

	return $result;
}
}

if (! function_exists('floordec')) {
/**
 * 返回不大于 value 的下一个小数(指定小数位数)
 *
 * @param  float  $value
 * @param  integer $decimals 小数位数
 * @return float
 */
function floordec(float $value, int $decimals = 2)
{
	return floor($value * pow(10, $decimals)) / pow(10, $decimals);
}
}

if (! function_exists('ceildec')) {
/**
 * 返回不小于 value 的下一个小数(指定小数位数)，value 如果有更多的小数部分则进一位
 * @param  float  $value
 * @param  integer $precision 小数位数
 * @return float
 */
function ceildec(float $value, int $precision = 2)
{
	return ceil($value * pow(10, $precision)) / pow(10, $precision);
}
}

if (! function_exists('is_all_zero')) {
/**
 * 数字是否是0，或者数组全部是0
 *
 * @param  mixed  $x  数字或者数组
 * @param  boolean  $strict  严格判断，如果$x不为数字，则返回false
 * @return boolean    是否是0
 */
function is_all_zero($x, bool $strict = false)
{
	if (is_array($x))
	{
		foreach ($x as $v)
			if (!is_all_zero($v))
				return false;
	} else {

		if ($strict && !is_numeric($x)) return false;

		$x += 0;

		return empty($x);
	}

	return true;
}
}

if(! function_exists('byterev32')) {
/**
 * 反转一个32位变量(比如int，或1个字符)的所有位，比如二进制：00000000,00000000,10000000,10000001反转为10000001,00000001,00000000,00000000
 * @param  mixed $x
 * @return mixed
 */
function byterev32(int $x)
{
	$x = (($x >>  1) & 0x55555555) | (($x <<  1) & 0xaaaaaaaa) ;
	$x = (($x >>  2) & 0x33333333) | (($x <<  2) & 0xcccccccc) ;
	$x = (($x >>  4) & 0x0f0f0f0f) | (($x <<  4) & 0xf0f0f0f0) ;
	$x = (($x >>  8) & 0x00ff00ff) | (($x <<  8) & 0xff00ff00) ;
	$x = (($x >> 16) & 0x0000ffff) | (($x << 16) & 0xffff0000) ;
	return $x;
}
}

if(! function_exists('byte_pop32')) {
/**
 * 计算出一个32位变量的1的个数，比如：十进制 124 的二进制为 1111100，故1的个数为5
 * @param  mixed $x
 * @return int
 */
function byte_pop32(int $x)
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
}

if(! function_exists('bswap_64')) {
/**
 * int64转换大小端
 * @param  int    $x
 * @return int
 */
function bswap_64(int $x)
{
   return ((($x & 0xff00000000000000) >> 56)
	  | (($x & 0x00ff000000000000) >> 40)
	  | (($x & 0x0000ff0000000000) >> 24)
	  | (($x & 0x000000ff00000000) >> 8)
	  | (($x & 0x00000000ff000000) << 8)
	  | (($x & 0x0000000000ff0000) << 24)
	  | (($x & 0x000000000000ff00) << 40)
	  | (($x & 0x00000000000000ff) << 56));
}
}

if(! function_exists('bswap_32')) {
/**
 * int32转换大小端
 * 如果输入int64，会自动截取低4位
 *
 * @param  int    $x
 * @return int
 */
function bswap_32(int $x)
{
   return (($x & 0xff000000) >> 24) | (($x & 0x00ff0000) >>  8) |  (($x & 0x0000ff00) <<  8) | (($x & 0x000000ff) << 24);
}
}

if(! function_exists('JSHash')) {
/**
 * 由Justin Sobel编写的按位散列函数
 */
function JSHash(string $string, int $len = null)
{
	$hash = 1315423911;
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash ^= (($hash << 5) + ord($string[$i]) + ($hash >> 2));
	}
	return $hash;
}
}

if(! function_exists('PJWHash')) {
/**
 * 该哈希算法基于AT＆T贝尔实验室的Peter J. Weinberger的工作。
 * Aho Sethi和Ulman编写的“编译器（原理，技术和工具）”一书建议使用采用此特定算法中的散列方法的散列函数。
 */
function PJWHash(string $string, int $len = null)
{
	$bitsInUnsignedInt = 4 * 8; //（unsigned int）（sizeof（unsigned int）* 8）;
	$threeQuarters = ($bitsInUnsignedInt * 3) / 4;
	$oneEighth = $bitsInUnsignedInt / 8;
	$highBits = 0xFFFFFFFF << (int) ($bitsInUnsignedInt - $oneEighth);
	$hash = 0;
	$test = 0;
	$len || $len = strlen($string);
	for($i = 0; $i < $len; $i++) {
		$hash = ($hash << (int) ($oneEighth)) + ord($string[$i]); } $test = $hash & $highBits; if ($test != 0) { $hash = (($hash ^ ($test >> (int)($threeQuarters))) & (~$highBits));
	}
	return $hash;
}
}

if(! function_exists('ELFHash')) {
/**
 * 类似于PJW Hash功能，但针对32位处理器进行了调整。它是基于UNIX的系统上的widley使用哈希函数。
 */
function ELFHash(string $string, int $len = null)
{
	$hash = 0;
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash = ($hash << 4) + ord($string[$i]); $x = $hash & 0xF0000000; if ($x != 0) { $hash ^= ($x >> 24);
		}
		$hash &= ~$x;
	}
	return $hash;
}
}

if(! function_exists('BKDRHash')) {
/**
 * 这个哈希函数来自Brian Kernighan和Dennis Ritchie的书“The C Programming Language”。
 * 它是一个简单的哈希函数，使用一组奇怪的可能种子，它们都构成了31 .... 31 ... 31等模式，它似乎与DJB哈希函数非常相似。
 */
function BKDRHash(string $string, int $len = null)
{
	$seed = 131;  # 31 131 1313 13131 131313 etc..
	$hash = 0;
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash = (int) (($hash * $seed) + ord($string[$i]));
	}
	return $hash;
}
}

if(! function_exists('SDBMHash')) {
/**
 * 这是在开源SDBM项目中使用的首选算法。
 * 哈希函数似乎对许多不同的数据集具有良好的总体分布。它似乎适用于数据集中元素的MSB存在高差异的情况。
 */
function SDBMHash(string $string, int $len = null)
{
	$hash = 0;
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash = (int) (ord($string[$i]) + ($hash << 6) + ($hash << 16) - $hash);
	}
	return $hash;
}
}

if(! function_exists('DJBHash')) {
/**
 * 由Daniel J. Bernstein教授制作的算法，首先在usenet新闻组comp.lang.c上向世界展示。
 * 它是有史以来发布的最有效的哈希函数之一。
 */
function DJBHash(string $string, int $len = null)
{
	$hash = 5381;
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash = (int) (($hash << 5) + $hash) + ord($string[$i]);
	}
	return $hash;
}
}

if (! function_exists('zend_inline_hash_func')) {
/**
 * PHP内置HASH KEY生成函数 也就是DJBHash
 *
 * @example 制造KEY冲突
 * $size = pow(2, 16);
 * for ($key = 0, $maxKey = ($size - 1) * $size; $key <= $maxKey; $key += $size) {
 *	echo $key .'--'. zend_inline_hash_func($key);
 * }
 *
 * @param  string $arKey 输入KEY
 * @return integer        返回hash key
 */
function zend_inline_hash_func(string $arKey)
{
	return DJBHash($arKey);
}
}

if(! function_exists('DEKHash')) {
/**
 * Donald E. Knuth在“计算机编程艺术第3卷”中提出的算法，主题是排序和搜索第6.4章。
 */
function DEKHash(string $string, int $len = null)
{
	$len || $len = strlen($string);
	$hash = $len;
	for ($i = 0; $i < $len; $i++) {
		$hash = (($hash << 5) ^ ($hash >> 27)) ^ ord($string[$i]);
	}
	return $hash;
}
}

if(! function_exists('FNVHash')) {
/**
 * 参考 http://www.isthe.com/chongo/tech/comp/fnv/
 */
function FNVHash(string $string, int $len = null)
{
	$prime = 16777619; //32位的prime 2^24 + 2^8 + 0x93 = 16777619
	$hash = 2166136261; //32位的offset
	$len || $len = strlen($string);
	for ($i = 0; $i < $len; $i++) {
		$hash = (int) ($hash * $prime) % 0xFFFFFFFF;
		$hash ^= ord($string[$i]);
	}
	return $hash;
}
}
