<?php
namespace Addons\Core\Validation;
 
use Illuminate\Validation\Validator;
/**
 * 本Class主要是处理宽字符
 * 
 */
class AnsiExtended extends Validator {
  
	/**
	 * Allow only alphabets, spaces and dashes (hyphens and underscores)
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	protected function validateAnsi( $attribute, &$value, $parameters )
	{
		//always true
		return (bool) true;
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
            return Arr::get($this->data, $attribute);
        } elseif (is_array($value)) {
            return count($value);
        } elseif ($value instanceof File) {
            return $value->getSize() / 1024;
        }

        //宽字节按照字体的几个宽度计算，比如「微软雅黑」下，汉字占据两个显示宽度
        $rule = $this->getRule($attribute, 'Ansi');
        $ansiWidth = empty($rule) || empty($rule[1]) ? 1 : $rule[1];
 
        return strlen_ansi($value, NULL, $ansiWidth);
        //return mb_strlen($value);
    }
 
}   //end of class
 
 
//EOF