<?php

namespace Addons\Censor\Ruling;

use Addons\Censor\File\Localer;
use Addons\Censor\Exceptions\RuleNotFoundException;

class Ruler extends Localer {

    public function get($key, $ruleKeys, $replace = [], $locale = null)
    {
    	//get all
    	$validations = $this->getLine($key, $locale);

    	if (empty($validations))
    		throw new RuleNotFoundException('[validation] Key is not exists: '. $key);

    	$ruleKeys == '*' && $ruleKeys = array_keys($validations);
		!is_array($ruleKeys) && $ruleKeys = explode(',', $ruleKeys);

		$validations = array_only($validations, $ruleKeys);

		if (!empty($diff = array_diff(array_keys($validations), $ruleKeys)))
    		throw new RuleNotFoundException('[validation] Rule keys are not exists: '.implode(', ', $diff));

    	foreach($validations as $attribute => &$v)
    		$v['rules'] = $this->parseRules($attribute, $v['rules'], $replace);

    	return $validations;
    }

    private function parseRules($attribute, $rules, $replace)
    {
    	return new Rules($attribute, $rules, $replace);
    }

}