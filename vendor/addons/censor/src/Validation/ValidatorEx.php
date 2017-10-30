<?php

namespace Addons\Censor\Validation;

use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as BaseValidator;

/**
 * 本Class主要是处理宽字符的长度、Fields检索等
 *
 */
class ValidatorEx extends BaseValidator {

	/**
	 * Allow only alphabets, spaces and dashes (hyphens and underscores)
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	protected function validateAnsi( $attribute, $value, $parameters )
	{
		return true;
	}

	protected function validatePhone($attribute, $value, $parameters)
	{
		$pattern = '/[0-9\-\s]*/i';
		empty($parameters) && $parameters = ['zh-CN'];
		switch (strtolower($parameters[0])) {
			case 'us':
				break;
			case 'zh-CN': //cn
				//如：010-12345678、0912-1234567、(010)-12345678、(0912)1234567、(010)12345678、(0912)-1234567、01012345678、09121234567
				$pattern = '/^(((\+86|086|17951)[\-\s])?1([34578][0-9])[\-\s]?[0-9]{4}[\-\s]?[0-9]{4}|(^0\d{2}-?\d{8}$)|(^0\d{3}-?\d{7}$)|(^\(0\d{2}\)-?\d{8}$)|(^\(0\d{3}\)-?\d{7}$))$/';
				break;
		}
		return preg_match($pattern, $value);
	}

	protected function validateNotZero($attribute, $value, $parameters)
	{
		if (!is_numeric($value)) return true;
		$value += 0;
		return !empty($value);
	}

	protected function validateIdCard($attribute, $value, $parameters)
	{
		$pattern = '/[0-9\-\s]*/i';
		empty($parameters) && $parameters = ['zh-CN'];
		switch (strtolower($parameters[0])) {
			case 'us':
				$pattern = '/^\d{6}-\d{2}-\d{4}$/';
				break;
			case 'zh-CN': //cn
				$pattern = '/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/';
				if(strlen($value) == 18) {
					$idCardWi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ]; //将前17位加权因子保存在数组里
					$idCardY = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ]; //这是除以11后，可能产生的11位余数、验证码，也保存成数组
					$idCardWiSum = 0; //用来保存前17位各自乖以加权因子后的总和
					for($i = 0; $i < 17; $i++)
						$idCardWiSum += $value[$i] * $idCardWi[$i];
					$idCardMod = $idCardWiSum % 11;//计算出校验码所在数组的位置
					$idCardLast = $value[17];//得到最后一位身份证号码

					//如果等于2，则说明校验码是10，身份证号码最后一位应该是X
					if($idCardMod == 2){
						if(strtolower($idCardLast) != 'x')
							return false;
					} else {
						//用计算出的验证码与最后一位身份证号码匹配，如果一致，说明通过，否则是无效的身份证号码
						if($idCardLast != $idCardY[$idCardMod])
							return false;
					}
				}
				break;
		}
		return preg_match($pattern, $value);
	}

	/**
	 * Get the size of an attribute.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return mixed
	 */
	protected function getSize($attribute, $value)
	{
		$hasNumeric = $this->hasRule($attribute, $this->numericRules);

		// This method will determine if the attribute is a number, string, or file and
		// return the proper size accordingly. If it is a number, then number itself
		// is the size. If it is a file, we take kilobytes, and for a string the
		// entire length of the string will be considered the attribute size.
		if (is_numeric($value) && $hasNumeric) {
			return $value;
		} elseif (is_array($value)) {
			return count($value);
		} elseif ($value instanceof File) {
			return $value->getSize() / 1024;
		}

		//宽字节按照字体的几个宽度计算，比如「微软雅黑」下，汉字占据两个显示宽度
		$rule = $this->getRule($attribute, 'Ansi');
		$ansiWidth = empty($rule) || empty($rule[1]) ? 1 : intval($rule[1][0]);

		return strlen_ansi($value, NULL, $ansiWidth);
		//return mb_strlen($value);
	}

	/**
	 * Handle dynamic calls to class methods.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		$rule = Str::snake(substr($method, 8));

		if (method_exists($this, $method))
			return $this->$method(...$parameters);
		else if (isset($this->extensions[$rule])) {
			return $this->callExtension($rule, $parameters);
		}

		throw new BadMethodCallException("Method [$method] does not exist.");
	}

}
