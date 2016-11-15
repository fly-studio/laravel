<?php
/**
 * 黄金分割
 * 
 * @return float
 */
function golden_section()
{
	return (sqrt(5) - 1) / 2;
}

/* 
 Returns angle of line segment in degrees. Angle will be a positive 
 value between 0 and 360. 0 will be north, 90 east, 180 south, 270 west 
 Author: Robert Kohr 
*/ 
function get_angle($x1, $y1, $x2, $y2, $negative_y_is_up = 0){ 
  //differences in x and y 
	$x = $x2 - $x1; 
	$y = $y2 - $y1; 
	//y will need to be negated for where + y is down 
	if($negative_y_is_up){ 
		//useful in some applications 
		$y = -$y; 
	} 
	return (rad2deg(atan2($x, $y))+360)%360; 
} 


/**
 * 像Excel列头一样返回0 => A,1 => B,26 => AA,27 => AB
 * 
 * @param  integer $index 输入数字
 * @return string        返回字母
 */
function int_to_words($index)
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

/**
 * Excel列表号,转化为数字 A => 0 AA =>26 AB => 27
 * 
 * @param  string $words 输入字母
 * @return integer        返回数字
 */
function words_to_int($words)
{
	$words = strtoupper($words);
	if ($words == 'A') return 0;
	$r = 26;
	$index = 0;
	$len = strlen($words);
	for ($i=0; $i < $len; $i++) { 
		$ch = substr($words, $i, 1);
		$index = (ord($ch) - 65 + 1) * pow($r, $len - $i - 1);
	}
	return $index - 1;
}

/**
 * 平方根
 * 
 * @param  float $x 输入
 * @return float    [description]
 */
function squareroot ($x) {
	/*
	for ($i = $x / 2.0, $d = 1.0;
		$d > 1.0e-5; //精度0.00001
		$d = ($x - $i * $i) / 2.0 / $i, $i += $d,($d < 0) ? $d = -$d : $d);
	return $i;
	*/
	//牛顿迭代法快速寻找平方根
	static $epsilon = .0000001;
	$guess = 1.0; $n = 0;
	while ( abs($guess * $guess - $x) >= $epsilon) {
		$guess = ($x / $guess + $guess) / 2.0; 
	}
	return $guess;
	/*
	牛顿迭代法
	float xhalf = 0.5f*x;
	int i = *(int*)&x; // get bits for floating VALUE 
	i = 0x5f375a86- (i>>1); // gives initial guess y0
	x = *(float*)&i; // convert bits BACK to float
	x = x*(1.5f-xhalf*x*x); // Newton step, repeating increases accuracy
	x = x*(1.5f-xhalf*x*x); // Newton step, repeating increases accuracy
	x = x*(1.5f-xhalf*x*x); // Newton step, repeating increases accuracy
 
	return 1/x;
	*/
}

/**
 * 指数表达式，乘方数只支持正整数
 * 
 * @param  number $base 值
 * @param  integer $exp 乘方数
 * @return number    结果
 */
function power($base, $exp){
	$result = 1;
	while($exp > 0){
		($exp & 1) && $result *= $base;
		$base *= $base;
		$exp >>= 1;
	}
	return $result;
}

/**
 * 是否2的幂，比如 2,4,8,16,32,64,128
 * @return boolean 
 */
function is_pow_from_2($x)
{
	return (($x & ($x - 1)) == 0);
}

/**
 * 取绝对值（只支持int）
 * @param  int $x 
 * @return int
 */
function absolute($x)
{
	$y = $x >> 31;//>>S
	return ($x + $y) ^ $y;
}

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
		return FALSE;
	$average = array_average($a); //计算平均值
	$i = 0.00;
	foreach ($a as $key => $value)
		$i += pow($value + 0 - $average, 2);
	return $i / count($a);
}

/**
 * 计算标准差
 *
 * @param array $a 数字的数组
 * @return double
 */
function standard_deviation(array $a)
{
	$i = variation_and_standard_deviation($a);
	return is_numeric($i) ? sqrt($i) : FALSE;
}

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

/**
 * 对比小数是否相等
 * 默认$scale为0，只对比整数部分
 * 
 * @param  float  $left_operand  数字1
 * @param  float  $right_operand 数字2
 * @param  integer $scale        精确到小数后面多少位
 * @return integer               0:相等 1:大于 -1:小于
 */
function decimal_compare($left_operand , $right_operand, $scale = 0)
{

	$scale < 0 && $scale = 0;
	$digits  = pow(0.1, $scale);
	if (abs($left_operand - $right_operand) < $digits)
		return 0;
	else
		return $left_operand > $right_operand ? 1 : -1;
}

if (!function_exists('bccomp')) {
	function bccomp($left_operand, $right_operand, $scale = 0)
	{
		return decimal_compare($left_operand, $right_operand, $scale);
	}
}

/**
 * 取小数部分
 * @param  float $x         
 * @param  integer $precision 保留小数位数
 * @return float            取得的小数部分
 */
function decimal($x, $precision = NULL)
{
	return bcsub($x ,floor($x), $precision);
}

/**
 * 大数字的LOG
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
		return FALSE;
	
	$max = $min = array();
	$max['a'] = max($a);
	$min['a'] = min($a);
	$max['b'] = max($b);
	$min['b'] = min($b);
	if ($max['a'] == 0 && $min['a'] == 0) return $b;
	if ($max['b'] == 0 && $min['b'] == 0) return FALSE;

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

/**
 * 概率算法
 * @example
 * $list = array(10,50,20,30,10); 默认50会出奖最高
 * 
 * @param  array $list 
 * @return mixed         返回KEY
 */
function probability_rand($list) { 
	$result = FALSE; 
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
/**
 * 返回不大于 value 的下一个小数(指定小数位数)
 * 
 * @param  float  $value
 * @param  integer $decimals 小数位数
 * @return float
 */
function floordec($value, $decimals = 2)
{    
	return floor($value * pow(10, $decimals)) / pow(10, $decimals);
}
/**
 * 返回不小于 value 的下一个小数(指定小数位数)，value 如果有更多的小数部分则进一位
 * @param  float  $value
 * @param  integer $precision 小数位数
 * @return float
 */
function ceildec($value, $precision = 2)
{
    return ceil($value * pow(10, $precision)) / pow(10, $precision);
}

// duplicates m$ excel's ceiling function
if( !function_exists('ceiling') )
{
    function ceiling($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number / $significance) * $significance) : false;
    }
}

/**
 * 数字是否是0，或者数组全部是0
 * @param  mixed  $x  数字或者数组
 * @param  boolean  $strict  严格判断，如果$x不为数字，则返回FALSE
 * @return boolean    是否是0
 */
function is_zero($x, $strict = FALSE)
{
	if (is_array($x))
	{
		foreach ($x as $v)
		{
			if ($strict && !is_numeric($v)) return FALSE;
			$v += 0;
			if (!empty($v)) return FALSE;
		}
	} else {
		if ($strict && !is_numeric($x)) return FALSE;
		$x += 0;
		return empty($x);
	}
	return TRUE;
}

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