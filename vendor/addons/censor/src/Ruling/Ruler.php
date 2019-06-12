<?php

namespace Addons\Censor\Ruling;

use Illuminate\Support\Arr;
use Addons\Censor\File\Localer;
use Addons\Censor\Exceptions\RuleNotFoundException;

class Ruler extends Localer {

	public function get($key, $ruleKeys, $replace = [], $locale = null)
	{
		//get all
		$validations = $this->getLine($key, $locale);

		if (empty($validations))
			throw new RuleNotFoundException('[Censor] Censor KEY is not exists: ['. $key. ']. You may create it.', $this, $key);

		$ruleKeys == '*' && $ruleKeys = array_keys($validations);
		!is_array($ruleKeys) && $ruleKeys = explode(',', $ruleKeys);

		$validations = Arr::only($validations, $ruleKeys);

		if (!empty($diff = array_diff($ruleKeys, array_keys($validations))))
			throw new RuleNotFoundException('[Censor] Rule keys are not exists: ['.implode(', ', $diff). '].', $this, $key);

		foreach($validations as $attribute => &$v)
			$v['rules'] = $this->parseRules($attribute, $v['rules'], $replace);

		return $validations;
	}

	private function parseRules($attribute, $rules, $replace)
	{
		return new Rules($attribute, $rules, $replace);
	}

}
