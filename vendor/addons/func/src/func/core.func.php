<?php
/** Checks a variable to see if it should be considered a boolean true or false.
 *     Also takes into account some text-based representations of true of false,
 *     such as 'false','N','yes','on','off', etc.
 * @author Samuel Levy <sam+nospam@samuellevy.com>
 * @param mixed $in The variable to check
 * @param bool $strict If set to false, consider everything that is not false to
 *                     be true.
 * @return bool The boolean equivalent or null
 */
if(! function_exists('boolval')) {
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

if(! function_exists('get_type')) {
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
}

/**
 * 交换两个数字，如果传入字符串，会截断长的那个字符串
 * @param  number $a 数字1
 * @param  number $b 数字2
 */
if(! function_exists('swap')) {
function swap(&$a, &$b)
{
	$a ^= $b ^= $a ^= $b; //根据C99标准，这种写法其实是undefined behavior
	//$a ^= $b; $b ^= $a; $a ^= $b;
}
}



if (!function_exists('get_namespace')) {
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
if (! function_exists('set_property')) {
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
if (! function_exists('get_property')) {
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
if (! function_exists('call_class_method_array')) {
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
if (! function_exists('call_class_method')) {
function call_class_method($class, $method, ...$parameters)
{
	if (!is_object($class)) throw new Exception('paramater #0 must be an object\'s instance.', 1);

    $reflectionMethod = (new ReflectionClass($class))->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($class, $parameters);
}
}
