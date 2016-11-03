<?php
/**
 * 一些数组处理函数
 *
 * @author Fly Mirage <no email>
 */


/**
 * Tests if an array is associative or not.
 *
 *     // Returns TRUE
 *     Arr::is_assoc(array('username' => 'john.doe'));
 *
 *     // Returns FALSE
 *     Arr::is_assoc('foo', 'bar');
 *
 * @param   array   $array  array to check
 * @return  boolean
 */
function is_assoc(array $array)
{
	//return (bool)count(array_filter(array_keys($array), 'is_string'));

	// Keys of the array
	$keys = array_keys($array);

	// If the array keys of the keys match the keys, then the array must
	// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
	return array_keys($keys) !== $keys;
}

/**
 * 将一个非数组的变量转成数组
 *
 * @param  mixed $data 输入字符/数字或数组等
 * @param  bool $strict 开启严格模式，NULL返回NULL，否则返回array(NULL)
 * @return array      返回数组
 */
function to_array($data, $strict = TRUE)
{
	if ($strict && is_null($data)) return $data;
	return !is_array($data) ?  array($data) : $data;
}

/**
 * 合并通过数据库查出来的数据集,比如$a = array(0=>array('f1'=>'v1','f2'=>'v2'));$b = array(0=>array('f3'=>'v3','f4'=>'v4'));
 *
 * @param array $a 数据集1
 * @param array $b 数据集2
 * @return array 返回如:$c = array(0=>array('f1'=>'v1','f2'=>'v2','f3'=>'v3','f4'=>'v4'));
 */
function dataset_merge(array $a,array $b) //合并数据集
{
	$d = array();
	foreach($a as $k => $v) {
		if (array_key_exists($k,$b))
			$d[$k] = array_merge((array)$v,(array)$b[$k]);
		else
			$d[$k] = $v;
	}
	foreach($b as $k => $v) {
		if (!array_key_exists($k,$d)) {
			$d[$k] = $v;
		}
	}
	return $d;
}
/**
 * 按照value删除数组对应的项
 *
 * @param array $haystack 需要操作的array
 * @param mixed $needles 需要删除的value,可以是value的数组或一个value的字符串
 * @param boolean $strict 是否强制对比类型
 * @return array 返回操作结果
 */
function array_delete(array &$haystack, $needles, $strict = FALSE)
{
	$needles = toarray($needles);
	$_haystack = $haystack;
	foreach($needles as $needle) {
		$indexes = array_keys($_haystack,$needle,$strict);
		foreach($indexes as $index) {
			unset($_haystack[$index]);
		}
	}
	return $_haystack;
}
/**
 * 以一个数组为基础,完善另外一个数组中没有的项目(并且会删除多余的项)
 * 优先以$arr_set的值为准，比如：
 * $set = array('k1' => 'v1','k5' => 'v2'); $base = array('k1' => 'default','k2' => 'default');
 * 使用 _extends($set,$base);得到结果为 array('k1' => 'v1','k2' => 'default'); 故结果以$base为蓝本，但是$a里面原有的值不会覆盖
 * [注意] 当$base中出现NULL时，默认匹配标量(NULL在PHP中属于非标量)
 *
 * @example 复杂的例子如：
 * $set = array(				$base = array(
 *	 'a' => array('e','a'),			'a' => array(),				使用$set['a'],有值,类型一致,均为非标量,($base['a']空数组,则表示$set['a']为数组即可)
 *	 'b' => '2a',					'b' => 5, 					使用$set['b'],有值,类型一致,均为标量
 *	 'c' => array('e','g'),			'c'=> 3, 					使用$base['c'],因为类型不一致,
 *	 'd' => NULL,					'd' => 5, 					使用$base['c'],因为$set['c']为NULL,
 *	 'e' => 123,					'e' => array(), 			使用$base['e'],因为类型不一致,(虽然$base中为空数组,但是$set中类型不一致,仍然使用$base)
 *									'f' => '33',				使用$base['f'],因为无$set['f']
 *	 'g' => array('k' => '222'),	'g' => array('k' => '111'),	递归到子项,匹配规则同上
 * );							);
 *
 * @param array $arr_set 需要调整的数组
 * @param array $arr_base 基础数组
 * @return array 返回调整之后的结果
 */
function _extends(&$arr_set, array &$arr_base) //以arr_base为默认,填补arr_set缺的项
{
	$result = array();
	if (is_array($arr_set)) {
		foreach($arr_base as $k => $v) {
			$is_set = isset($arr_set[$k]);
			$is_array = $is_set && is_array($arr_set[$k]);
			$result[$k] = $v; //先设置默认值
			if ($is_set) {
				if (is_array($v) && !empty($v)) //base为数组并且非空
					$result[$k] = _extends($arr_set[$k],$v); //则递归循环
				elseif (is_null($v))
					is_scalar($arr_set[$k]) && $result[$k] = $arr_set[$k];  //如果base为NULL，匹配任意标量即可
				else
					is_scalar($v) == is_scalar($arr_set[$k]) && $result[$k] = $arr_set[$k];//如果base和set的类型一致,则使用set的值
			}
		}
	} else {
		$result = $arr_base;
	}
	return $result;
}
/**
 * 合并时，后者覆盖前者的值（array_merge_recursive会将相同KEY合并数组）
 * @param  array $arr1 
 * @param  array $arr2 
 * @param  array ... 
 * @return array 返回合并后的结果
 */
function array_merge_recursive_overwrite()
{

	$arrays = func_get_args();
	$base = array_shift($arrays);

	foreach ($arrays as $array) {
		reset($base); //important
		while (list($key, $value) = @each($array)) {
			if (is_array($value) && @is_array($base[$key])) {
				$base[$key] = array_merge_recursive_overwrite($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
}
/**
 * 按key1/key2/key3的方式添加一个项,相当于$src['key1']['key2']['key3'] = $value
 *
 * @param array $src 需要进行添加操作的数组
 * @param string $key 以"key1/key2/key3/key4/key5"的方式传递,可以无限级
 * @param mixed $value 该项的值
 * @return array 得到操作后的数据
 */
function array_add_recursive(array &$src, $key, $value)
{
	$keys = explode('/',$key);
	$_src = $src;
	$t = &$_src;
	foreach($keys as $v){
		if (!isset($t[$v])) $t[$v] = array();
		$t = &$t[$v];
	}
	$t = $value;
	return $_src;
}
/**
 * 按key1/key2/key3的方式删除一个项,相当于unset($src['key1']['key2']['key3'])
 *
 * @param array $src 需要进行删除操作的数组
 * @param string $key 以"key1/key2/key3/key4/key5"的方式传递,可以无限级\
 * @return array 得到操作后的数据
 */
function array_delete_recursive(array &$src, $key)
{
	$keys = explode('/',$key);
	$_src = $src;
	$t = &$_src;
	$count = count($keys) - 1;
	for($i = 0; $i < $count; ++$i) { //最后一个不循环
		$k = $keys[$i];
		if (!isset($t[$k])) return;
		$t = &$t[$k];
	}
	$k = $keys[$count]; //获取最后一个
	if(isset($t[$k]) && is_array($t))
		unset($t[$k]); //删除最后一个
	return $_src;
}

/**
 * 按key1/key2/key3的方式获取一个项,相当于得到$value = $src['key1']['key2']['key3']，没有则返回NULL
 *
 * @param array $src 原始数组
 * @param string $key 以"key1/key2/key3/key4/key5"的方式传递,可以无限级
 * @return mixed 该项的值
 */
function array_get_recursive(array &$src, $key)
{
	$keys = explode('/',$key);
	$_src = $src;
	$t = &$_src;
	foreach($keys as $v){
		if (!isset($t[$v])) return NULL;
		$t = &$t[$v];
	}
	$value = $t;
	return $value;
}

/**
 * 按key1/key2/key3的方式设置一个项,相当于$src['key1']['key2']['key3'] = $value
 * 其实和array_add_recursive功能一致
 *
 * @param array $src  需要进行添加操作的数组
 * @param string $key  以"key1/key2/key3/key4/key5"的方式传递,可以无限级
 * @param miexed $value 该项的值
 * @return array 得到操作后的数据
 */
function array_set_recursive(array &$src, $key, $value)
{
	return array_add_recursive($src, $key, $value);
}

/**
 * private 函数,切分$selector的sub key,独立出来供某些地方调用
 * @example 如果$with_whitespace为FALSE，$selector会自动去掉空格，比如: [a, ;b ] 相当于[a,;b]，会自动删除中间和最后那个空格
 * @example 如果$with_empty为FALSE，$selector会自动去掉重复分隔符，比如: [a,;b] 相当于[a,b]，不然会返回：[a,空,b]
 *
 * @param string $selector  条件表达式  [,][;] (逗号，分号)表示"或"
 * @param boolean $with_whitespace 是否允许包含空格
 * @param boolean $with_empty 是否包含空值
 * @return array 返回切分后的数组
 * 
 * 
 */
function _array_selector_subkey($selector, $with_whitespace = FALSE, $with_empty = FALSE)
{
	if (is_array($selector)) return $selector;

	!$with_whitespace && $selector = preg_replace('/\s*([,;])\s*/', '$1', $selector); //去掉这些分隔符前后的空格
	return $with_empty ? preg_split('/[,;]/', $selector) : preg_split('/[,;]+/', $selector, NULL, PREG_SPLIT_NO_EMPTY);
}

/**
 * private 函数,切分$selector,外界一般不调用
 * @example 如果$with_whitespace为FALSE，$selector会自动去掉空格，比如: [/ a, ;b / /] 相当于[/a,;b//]，会自动删除最前的那个空格，以及中间那些和最后那个空格
 * @example 如果$with_empty为FALSE，$selector会自动去掉重复分隔符，比如: [a,;b///*] 相当于[a,b/*]，不然会返回：[[a,空,b]/[空]/[空]/*]
 *
 * @param string $selector  条件表达式 [/] 表示数组维度;  [,][;] (中竖线，逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY；
 * @param boolean $with_whitespace 是否允许包含空格
 * @param boolean $with_empty 是否包含空值
 * @return array 返回切分后的数组
 * 
 */
function _array_selector_keymaker($selector, $with_whitespace = FALSE, $with_empty = FALSE)
{
	if (is_array($selector)) return $selector;

	$keys = array();
	!$with_whitespace && $selector = preg_replace('/\s*([\\/\\|])\s*/', '$1', $selector); //去掉这些分隔符前后的空格
	
	$root = preg_split('/\\|+/', $selector, NULL, PREG_SPLIT_NO_EMPTY); //切分|
	foreach ($root as $value) {
		$sp = $with_empty ? preg_split('/\\//', $value) : preg_split('/\\/+/', $value, NULL, PREG_SPLIT_NO_EMPTY);
		foreach($sp as $k => $v) {
			!$with_whitespace && $v = trim($v);
			$f = empty($v) || $v == '*' ? '*' : _array_selector_subkey($v, $with_whitespace, $with_empty);
			$keys[$value][$k] = $f;
		}
	}

	//print_r($keys);
	return $keys;
}
/**
 * 获取数组下面的某些项的集合
 * 如 $a = array('a' => array('f1'=>'v1','f2'=>'v2','f3'=>'v3'),'b' => array('f1'=>'v4','f2'=>'v5'),'c'=>array('f1'=>'v6'));
 * 如 $selector = 'a,b/f1'; 得到 array('a' => array('f1'=>'v1'),'b' => array('f1'=>'v4'));
 * 如 $selector = 'a/*'; 得到 array('a' => array('f1'=>'v1','f2'=>'v2','f3'=>'v3'));
 *
 * @param array $data  数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [[,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回根据表达式计算出来的值
 */
function array_selector(array $data, $selector = '*')
{
	if (!is_array($data)) return false;
	$result = array();
	//print_r($result);
	if (!empty($selector)) {

		$root = _array_selector_keymaker($selector);
		foreach($root as $keys)
		{
			$_data = $data;
			_array_selector_rev($_data, $keys); //
			$result = array_merge_recursive_overwrite($result, $_data);
		}
	}
	return $result;
}
/*array_selector 的 private 函数,外界一般不调用*/
function _array_selector_rev(array &$data, array $keys, $level = 0)
{
	if (is_array($data)) {
		$count = count($keys) - 1;
		foreach($data as $key => $value) {
			if ($keys[$level] != '*' && !in_array($key, $keys[$level])) {
				unset($data[$key]);
				continue;
			}
			if ($level < $count)
				_array_selector_rev($data[$key], $keys, $level + 1);
		}
	}
}
/**
 * 获取数组下面的某些项的集合
 * array_selector的别名函数
 *
 * @param array $data  数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [[,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回根据表达式计算出来的值
 */
function array_get_selector(array $data, $selector = '*')
{
	return array_selector($data, $selector);
}


/**
 * 数组下面的某些项添加一些数据,没有维度限制
 * 如 $a = array('a' => array('f1' => 'v1'),'b' => array('f1' => 'v4'),'c'=>array('f1' => 'v6'));
 * 需要传入$add_content为array('add1' => 123,'add2' => 234);
 * 如 $selector = 'a,c'; 得到 array('a' => array('f1' => 'v1','add1' => 123, 'add2' => 234),'b' => array('f1' => 'v4'), 'c' => array('f1' => 'v6','add1' => 123,'add2' => 234));
 * 如 $selector = '*'; 得到 array('a' => array('f1' => 'v1','add1' => 123, 'add2' => 234),'b' => array('f1' => 'v4','add1' => 123,'add2' => 234), 'c' => array('f1' => 'v6','add1' => 123,'add2'=>234));
 *
 * @param array $data 原始数组
 * @param array $add_content 需要添加的内容,必须是数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回根据表达式计算出来的值
 */
function array_add_selector(array $data, array $add_content, $selector = '*')
{
	if (!is_array($data)) return false;
	$result = $data;
	if (!empty($selector)) {
		$root = _array_selector_keymaker($selector);
		foreach($root as $keys)
			_array_add_selector_rev($result, $keys, $add_content);
	}
	return $result;
}
/*array_add_selector 的 private 函数,外界一般不调用*/
function _array_add_selector_rev(array &$data, array $keys, array &$add_content, $level = 0)
{
	if (is_array($data)) {
		$count = count($keys) - 1;
		foreach ($data as $key => $value) {
			if ($keys[$level] == '*' || in_array($key, $keys[$level])) {
				if ($level < $count)
					_array_add_selector_rev($data[$key],$keys,$add_content,$level + 1);
				else if ($level == $count)
					$data[$key] = array_merge($data[$key],$add_content);
			}
		}
	}
}
/**
 * 删除数组下面的某些项,没有维度限制
 * 如 $a = array('a' => array('f1' => 'v1', 'f2' => 'v2'),'b' => array('f1' => 'v4','f2' => 'v5'),'c'=>array('f1' => 'v6'));
 * 如要删除 'f1';
 * 如 $selector = 'a,c/f1'; 得到 array('a' => array('f2' => 'v2'),'b' => array('f1' => 'v4','f2' => 'v5'),'c'=>array());
 * 如 $selector = '* /f1' (无空格，只是因为 * 和 / 拼写在一起代表注释结束); 得到 array('a' => array('f2' => 'v2'),'b' => array('f2' => 'v5'),'c'=>array());
 *
 *
 * @param array $data 原始数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回根据表达式计算出来的值
 */
function array_delete_selector(array $data, $selector = '*')
{
	if (!is_array($data)) return FALSE;
	$result = $data;
	if (!empty($selector)) {
		$root = _array_selector_keymaker($selector);
		foreach($root as $keys)
		{
			$delete_keys = array_pop($keys);
			if (empty($keys)) {	//只有一层，直接搞定
				foreach ($delete_keys as $value) {
					if(isset($result[$value])) unset($result[$value]);
				}
			} else
				_array_delete_selector_rev($result, $keys, $delete_keys);
		}
		
	}
	return $result;
}
/*array_delete_selector 的 private 函数,外界一般不调用*/
function _array_delete_selector_rev(array &$data, array $keys, $delete_keys, $level = 0)
{
	if (is_array($data)) {
		$count = count($keys) - 1;
		foreach ($data as $key => $value) {
			if ($keys[$level] == '*' || in_array($key, $keys[$level])) {
				if ($level < $count)
					_array_delete_selector_rev($data[$key],$keys,$delete_keys,$level + 1);
				else if ($level == $count) {
					if ($delete_keys == '*')
						$data[$key] = null;
					else {
						foreach ($delete_keys as $v) {
							if (isset($data[$key][$v]) && is_array($data[$key]) )
								unset($data[$key][$v]);
						}
					}
				}
			}
		}
	}
}

/**
 * 根据传递的$keys来筛选数组
 * 例如 $a = array('a' => 0, 'b' => 1, 'c' => 2, 'd' => 3);
 * 使用方法：array_keyfilter($a,'a,c') 或 array_keyfilter($a,array('a','c')); 得到结果：array('a' => 0,'c' => 2);
 *
 * @param array $data 原始数组
 * @param mixed $keys 需要筛选的keys,可以为array或使用逗号连接的字符串
 * @return array 返回筛选之后的内容
 */
function array_keyfilter(array $data, $keys)
{

	$keys = _array_selector_subkey($keys);
	$result = array();
	foreach ($data as $key => $value) {
		if (in_array($key, $keys))
			$result[$key] = $value;
	}
	return $result;
}

/**
 * 通过$selector来筛选数据
 * array_selector的别名函数，使用方法见array_selector
 *
 * @param array $data 原始数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回筛选之后的内容
 */
function array_keyfilter_selector(array $data, $selector = '*')
{
	return array_selector($data, $selector);
}

/**
 * 递归返回数据所有的值
 * 
 * @param  array $arr  输入数据
 * @return array       显示所有值
 */
function array_values_recursive(array $arr) {
	$arr = array_values($arr);
	while (list($k,$v)=each($arr))
	{
		if (is_array($v))
		{
			array_splice($arr,$k,1,$v);
			next($arr);
		}
	}
	return $arr;
}


/**
 * 将数组按照a/b/c的方式变为一维数组
 * @example array('a' => array('c' => 1,'d' => 2),'b' = array('c' => 4)); 得到结果 array('a/c' => 1, 'a/d' => 2,'b/c' => 4);
 *
 * @param array $data 原始数组
 * @param string $delimiter  分隔符;
 * @param string $key 前缀，比如 $key = ':'，将会得到 array(':a/c' => 1, ':a/d' => 2,':b/c' => 4);
 * @return array 返回筛选之后的内容
 */
function array_keyflatten(array $data, $delimiter = '/', $prefix_key = '')
{
	$_result = array();

	//隐藏参数 $level
	$level = 0;
	func_num_args() > 3 && $level = intval(func_get_arg(3));

	if (is_array($data) && !empty($data))
	{
		foreach ($data as $k => $value)
		{
			$_k = $prefix_key . ($level > 0 ? $delimiter : '') . $k;
			if (is_array($value))
				$_result += array_keyflatten($value, $delimiter, $_k, $level + 1);
			else
				$_result[$_k] = $value;
		}
	}
	elseif (is_array($data) && empty($data))
		$_result = $data;
	else
		$_result[$prefix_key] = $data;
	return $_result;
}
if (!function_exists('array_flatten'))
{
	function array_flatten(array $data, $delimiter = '/', $prefix_key = '')
	{
		return array_keyflatten($data, $delimiter, $prefix_key);
	}
}
/**
 * 根据表达式，提取第几层的数据，并把数据提到一维，如果$overwrite为TRUE，则相同的键值，后者会覆盖前者
 * @example $a = array('a' => array('c' => 1,'d' => 2),'b' => array('c' => 4, 'e' => 5));
 * @example $selector = '* /*' 结果 array('c' => 4, 'd' => 2, 'e' => 5); 后面的c覆盖了前面的c
 * @example $selector = '* /c' 结果 array('c' => 4);
 *
 * @param array $data 原始数组
 * @param string $selector  条件表达式 [/] 表示数组维度;  [,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array 返回筛选之后的内容
 */
function array_flatten_selector($data, $selector = '*', $overwrite = TRUE)
{

	if (!is_array($data)) return array();
	$result = array();
	if (!empty($selector)) {

		$root = _array_selector_keymaker($selector);
		foreach($root as $keys)
			$result = call_user_func($overwrite ? 'array_merge_recursive_overwrite' : 'array_merge_recursive', $result, _array_flatten_selector_rev($data, $keys, 0, $overwrite));
	}
	else
	{
		$is_assoc = is_assoc($data);
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$result =  call_user_func($overwrite ? 'array_merge_recursive_overwrite' : 'array_merge_recursive', $result, array_flatten_selector($value, $selector, $overwrite));
			}
			else
			{
				if ($is_assoc)
				{
					if ($overwrite)
						$result[$key] = $value;
					else
						$result[$key][] = $value;
				}
				else
				{
					$result[] = $value;
				}
			}
		}
	}
	return $result;
}

/*array_flatten_selector 的 private 函数,外界一般不调用*/
function _array_flatten_selector_rev(array &$data, array $keys, $level = 0, $overwrite = TRUE)
{

	$_result = array();

	if (is_array($data)) {
		$count = count($keys) - 1;
		foreach ($data as $key => $value)
		{
			if ($keys[$level] == '*' || in_array($key, $keys[$level]))
			{
				if ($level < $count)
				{
					$_result = call_user_func($overwrite ? 'array_merge_recursive_overwrite' : 'array_merge_recursive', $_result, _array_flatten_selector_rev($data[$key], $keys, $level + 1, $overwrite));
				}
				elseif ($level == $count)
				{
					if ($overwrite)
						$_result[$key] = $value;
					else
						$_result[$key][] = $value;

				}
			}
		}
	}

	return $_result;
}

/**
 * 将数组里面的某些项目转化为数组，注意：只转化表达式最后一级的项目
 * @example $a = array('a' => 1, 'b' => 2, 'c' => array('e','g'));
 * @example to_array_selector($a, '*') 得到 [ 'a' => [1], 'b' => [2], 'c' => ['e','g'] ]; 因为c本身就是数组,则不转化
 * @example to_array_selector($a, 'a,b') 任然为 [ 'a' => [1], 'b' => [2], 'c' => ['e','g'] ];
 * @example to_array_selector($a, '* /*') 得到 [ 'a' => 1, 'b' => 2, 'c' => [ ['e'], ['g'] ] ]; 只转化c下级的参数，只转化表达式最后一级的项目
 * @example to_array_selector($a, 'c/*') 任然为 [ 'a' => 1, 'b' => 2, 'c' => [ ['e'], ['g'] ] ];
 *
 * @param  array $data      输入的数据
 * @param  string $selector 条件表达式 [/] 表示数组维度;  [,][;] (逗号，分号)表示同维度KEY1和KEY2和KEYN...; [*] 表示同维度全部的KEY，此参数注意事项请见_array_selector_keymaker；
 * @return array            返回数据
 */
function to_array_selector(&$data, $selector = '*')
{
	if (!is_array($data)) return to_array($data);
	$result = $data;
	$root = _array_selector_keymaker($selector);
	foreach ($root as $keys)
		$result = _to_array_selector_rev($result, $keys, 0);
	return $result;
}

/*to_array_selector 的 private 函数，外界一般不调用*/
function _to_array_selector_rev(&$data, array $keys, $level = 0)
{
	if (!is_array($data)) return $data;

	$result = array();
	$count = count($keys) - 1;
	foreach ($data as $key => $value)
	{
		if ($keys[$level] == '*' || in_array($key, $keys[$level]))
		{
			if ($level < $count)
				$result[$key] = _to_array_selector_rev($data[$key], $keys, $level + 1);
			elseif ($level == $count)
				$result[$key] = to_array($value);
		}
		else
			$result[$key] = $value;
	}

	return $result;
}

/**
 * 在数组开头，添加一个带KEY的数据
 *
 * @param array $arr 原始数组
 * @param string $key 添加的KEY
 * @param mixed $val 添加的数据
 * @return array 返回添加后的结果
 */

function array_unshift_assoc(array $arr, $key, $val)
{
	// return array($key => $val) + $arr;
	$_arr = array_reverse($arr, true);
	$_arr[$key] = $val;
	return array_reverse($_arr, true);
}


/**
 * Function array_insert().
 *
 * Returns the new number of the elements in the array.
 *
 * @param array $array Array (by reference)
 * @param mixed $value New element
 * @param int $offset Position
 * @return int
 */
function array_insert(array &$array, $value, int $offset)
{
	if (is_array($array)) {
		$array  = array_values($array);
		$offset = intval($offset);
		if ($offset < 0 || $offset >= count($array))
		{
			array_push($array, $value);
		} elseif ($offset == 0) {
			array_unshift($array, $value);
		} else {
			$temp  = array_slice($array, 0, $offset);
			array_push($temp, $value);
			$array = array_slice($array, $offset);
			$array = array_merge($temp, $array);
		}
	} else {
		$array = array($value);
	}
	return count($array);
}



/**
 * Range as a string. Items are separated with a comma; which can be in any of the following formats:
 *
 * @example "1, 2, 3, 4, 5, 6" - output: 1, 2, 3, 4, 5, 6
 * @example "1 - 6"  - output: 1, 2, 3, 4, 5, 6
 * @example "1 -%2 6" - output: 1, 3, 5 (last number will not be counted unless it evenly fits in)
 * @example "1 - -6" - output: 1, 0, -1, -2, -3, -4, -5, -6
 * @example "0 - 0" - output: 0
 * @example "1, 2, 3, [LAST_NUM] - 6" - output: 1, 2, 3, 3, 4, 5, 6 (note repeated 3)
 * @example "1, 2, 3, [LAST_NUM+1] - 6" - output: 1, 2, 3, 4, 5, 6 (no repeated 3)
 * @example "1, 2, 3, [LAST_NUM+-1] - 6" - output: 1, 2, 3, 2, 3, 4, 5, 6
 *
 * @param string $range_str
 * @return mixed
 */
function range_string($range_str)
{
	$range_out = array();
	$ranges = explode(',', $range_str);

	$last_num = 0;

	foreach($ranges as $range)
	{
		$step = 1;
		$range = trim($range);

		if(is_numeric($range))
		{
			// Just a number; add it to the list.
			$range_out[] = $range;
			$last_num = $range;
		}
		else if(is_string($range))
		{
			// Figure out if it is just a character.
			if(strlen($range) == 1)
			{
				$range_out[] = (string)$range;
				$last_num = 0;
			}
			else
			{
				// Is probably a range of values.
				$range_exp = explode(' ', $range);

				if(substr($range_exp[1], 0, 1) == '-' && !is_numeric(substr($range_exp[1], 0, 1)))
				{
					// Jumping range?
					$jump = str_split($range_exp[1], 1);

					if(count($jump) > 0)
					{
						if(isset($jump[1]) && $jump[1] == '%')
						{
							$step = substr($range_exp[1], 2);
						}
					}
					else
					{
						// Normal range.
						$step = 1;
					}
				}
				else
				{
					$step = 1;
				}

				if($range_exp[0] == '[LAST_NUM]')
				{
					$start = $last_num;
				}
				else
				{
					$exp = explode("+", $range_exp[0]);

					if($exp[0] == '[LAST_NUM')
					{
						$start = $last_num + trim($exp[1], ']');
					}
					else
					{
						$start = $range_exp[0];
					}
				}

				$end = $range_exp[2];

				if($start > $end)
				{
					for($i = $start; $i >= $end; $i -= $step)
					{
						$range_out[] = $i;
					}

					$last_num = $i;
				}
				else
				{
					for($i = $start; $i <= $end; $i += $step)
					{
						$range_out[] = $i;
					}

					$last_num = $i;
				}

				// echo $step . ", ";
			}
		}
	}
	return $range_out;
}
/**
 * 移动到匹配性的下一个
 * @example value_next('DESC', array('ASC', 'DESC', 'OTHER')) 返回 OTHER
 * @example value_next('DESC', array('ASC', 'DESC')) 返回 ASC，匹配项在结尾时，会返回第一项
 * @example value_next(NULL, array('ASC', 'DESC')) 返回 ASC，如果没有找到，则返回第一项
 * 
 * @param  mixed $needle   需要匹配的value
 * @param  array  $haystack 输入数组
 * @return mixed          匹配的下一个value
 */	
function value_next($needle, array $haystack)
{
	$size = count($haystack);
	if (empty($size)) return NULL;

	$_data = array_values($haystack);
	$index = array_search($needle, $_data);
	if ($index === FALSE || ++$index >= $size)
		$index = 0;
	return $_data[$index];
}

/**
 * 查询$needle中的值是否全部在$haystack中
 * @example [1,4] in [1,2,3,4]
 * @example [1,4,5] not in [1,2,3,4]
 * 
 * 
 * @param  array  $needles   需要检查的数组
 * @param  array  $haystack  原始数组
 * @return bool              $needles是否完全存在$haystack中
 */
function array_in_array(array $needles, array $haystack)
{
	//简单办法，遍历数组，查找是否有不存在的值
	// foreach ($needles as $needle) {
	// 	if (!in_array($needle, $haystack))
	// 		return FALSE;
	// }
	// return TRUE; //均存在

	//高阶,查看交集的数量是否等于$needles的数量，使用内置C，效率高
	return count($needles) == count(array_intersect($needles, $haystack));
}

/**
 * 通过一个数组,分析出其中的某些字段,并返回
 * @example
 * $a = [['a'=>1],['a'=>4,'b'=>2],['a'=>34,'c'=>25],['b'=>24,'c'=>56],];
 * search_fields($a,'a,c') 得到结果:[1,3,34,25,56];
 * 
 * @param  array $data 输入的数据
 * @param  string $keys 需要查找的KEY,可以多项
 * @return array       返回一个符合该KEY的数组
 */
function search_fields($data, $keys)
{
	if (empty($data)) return array();
	$keys = _array_selector_subkey($keys);
	$result = array();
	foreach ($data as $key => $value) {
		if (is_array($value))
			$result = array_merge($result, search_fields($value, $keys));
		else
		{
			if (in_array($key, $keys))
				$result[] = $value;
		}
	}
	return array_unique($result);
}

/**
 * 从某索引取到另外一个索引
 * 不包含end_offset
 *
 */
function array_slice_byoffset(&$arr, $start_offset, $end_offset, $preserve_keys = FALSE)
{
	return array_slice($arr, $start_offset, $end_offset - $start_offset, $preserve_keys);
	/*
	$result = array();
	foreach($arr as $k => $v) {
		if ($k >= $start_offset && $k < $end_offset) {
			if ($preserve_keys) {
				$result[$k] = $v;
			} else {
				$result[] = $v;
			}
		}
	}
	return $result;
	*/
}

/**
 * 对多维数组进行排序，类似于 SQL 的 ORDER BY 子句的功能
 * @example
 * array_orderby ( $data, string $col_name [, mixed $order_by [, mixed $order_type [, array $... ]]] )
 *
 * @param array $data 传入原始数组
 * @param string $col_name 列名
 * @param int $order_by 同array_multisort，SORT_ASC/SORT_DESC，可选
 * @param int $order_type 同array_multisort，SORT_REGULAR/SORT_NUMERIC/SORT_STRING，可选
 * @param 重复
 * @return array 返回已排序的数组
 */
function array_orderby()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
			$args[$n] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

/**
 * PHP内置HASH KEY生成函数
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
function zend_inline_hash_func($arKey)
{
		$hash = 5381;
		$nKeyLength = strlen($arKey);
		/* variant with the hash unrolled eight times */
		for($i = 0; $i < $nKeyLength; ++$i)
			$hash = (($hash << 5) + $hash) + ord($arKey{$i});
		return $hash;
}

/**
 * 计算数组平均数
 *
 * @param array $a 数字的数组
 * @return double
 */
function array_average(array $a)
{
	return !empty($a) ? array_sum($a) / count($a) : FALSE;
}
/**
 * 从数组中随机取出一些值，类似array_rand，但是返回的是数据，而不是KEY
 * 
 * @param  array $data 数组
 * @param  int $num    需要随机取出多少数据
 * @return array       返回结果
 */
function array_pick($data, $num) { 
	$count = count($data); 
	if ($num <= 0) return array(); 
	if ($num >= $count) return $data; 
	$required = $count - $num; 
	$keys = array_rand($data, $required); 
	$keys = to_array($keys);
	foreach ($keys as $k) unset($data[$k]);
	return $data; 
} 

/**
 * 打乱数组，保留KEY
 *
 * @param  array $array 需要打乱的数组
 * @return boolean      永远输出TRUE
 */
function shuffle_assoc( array &$array ) 
{ 
	$keys = array_keys( $array ); 
	shuffle( $keys );

	$random = array(); 
	foreach ($keys as $key) 
		$random[$key] = $array[$key];
	//$random = array_merge_recursive_overwrite( array_flip( $keys ) , $array ); 
	$array = $random;
	return TRUE;
}

/**
 * 递归查找数据中最大的值
 * 
 * @param  mixed  $array 一个多维数组，或者数字
 * @return mixed        最大的数据
 */
function array_max_recursive(array $array /*, ...*/) {
	$max = NULL;
	$stack = func_get_args();
	do {
		$current = array_pop($stack);
		if (is_array($current))
		{
			foreach ($current as $value)
			{
				if (is_array($value))
				{
					$stack[] = $value;
				} else {
					// max(NULL, 0) returns NULL, so cast it
					$max = max($max, $value);
				}
			}
		} else
			$max = max($max, $current);
	} while (!empty($stack));

	return $max;
}

/**
 * 递归查找数据中最小的值
 * 
 * @param  mixed  $array 一个多维数组，或者数字
 * @return mixed        最小的数据
 */
function array_min_recursive(array $array /*, ...*/) {
	$min = NULL;
	$stack = func_get_args();
	do {
		$current = array_pop($stack);
		if (is_array($current))
		{
			foreach ($current as $value)
			{
				if (is_array($value))
				{
					$stack[] = $value;
				} else {
					// min(NULL, 0) returns NULL, so cast it
					$min = min($min, $value);
				}
			}
		} else
			$min = min($min, $current);
	} while (!empty($stack));

	return $min;
}