<?php

namespace Addons\Censor;

use Illuminate\Support\Str;
use Addons\Censor\Ruling\Ruler;
use Illuminate\Contracts\Validation\Factory;

class Censor {

	protected $data;
	protected $censorKey;
	protected $attributes;
	protected $replace;
	protected $validations;

	public function __construct(Ruler $ruler, $censorKey, $attributes, $replace = [], $locale = null)
	{
		$this->ruler = $ruler;
		$this->censorKey = $censorKey;
		$this->replace = $replace;
		$this->validations = $ruler->get($censorKey, $attributes, $replace, $locale);
		$this->attributes = array_keys($this->validations);
	}

	public function validData()
	{
		return array_only($this->parseData($this->data), $this->attributes);
	}

	public function data($data = null)
	{
		if (is_null($data))
			return ($this->data);

		$this->data = $data;
		return $this;
	}

	protected function parseData($data)
	{
		$newData = [];

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$value = $this->parseData($value);
			}

			// If the data key contains a dot, we will replace it with another character
			// sequence so it doesn't interfere with dot processing when working with
			// array based validation rules and array_dot later in the validations.
			if (Str::contains($key, '.')) {
				$newData[str_replace('.', '->', $key)] = $value;
			} else {
				$newData[$key] = $value;
			}
		}

		return $newData;
	}

	public function attributes()
	{
		return $this->attributes;
	}

	public function censorKey()
	{
		return $this->censorKey;
	}

	public function replace()
	{
		return $this->replace;
	}

	public function messagesWithDot()
	{
		return array_dot($this->messages());
	}

	public function messages()
	{
		$messages = [];
		foreach($this->validations as $attribute => $line)
		{
			if (!isset($line['message']))
				continue;
			$messages[$attribute] = $line['message'];
		}
		return $messages;
	}

	public function messageWithTranslate()
	{
		$validator = $this->validator();
		$messages = [];
		foreach($this->validations as $attribute => $line)
		{
			if (!isset($line['message']))
				continue;
			foreach($line['message'] as $rule => $text)
				$messages[$attribute][$rule] = $validator->makeReplacements($text, $line['name'], $rule, $line['rules']->ruleParameters($rule) ?? []);
		}
		return $messages;

	}

	public function names()
	{
		$names = [];
		foreach($this->validations as $attribute => $line)
		{
			if (!isset($line['name']))
				continue;
			$names[$attribute] = $line['name'];
		}
		return $names;
	}

	public function originalRules()
	{
		$rules = [];
		foreach($this->validations as $attribute => $line)
			$rules[$attribute] = $line['rules']->originalRules();
		return $rules;
	}

	public function rules()
	{
		$rules = [];
		foreach($this->validations as $attribute => $line)
			$rules[$attribute] = $line['rules']->rules();
		return $rules;
	}

	public function jsRules()
	{
		$rules = [];

		foreach($this->validations as $attribute => $line)
			$rules = array_merge_recursive($rules, $line['rules']->js());

		return $rules;
	}

	public function validator()
	{
		return $this->getValidationFactory()->make($this->data() ?? [], $this->originalRules(), $this->messagesWithDot(), $this->names());
	}

	public function js()
	{
		return [
			'rules' => $this->jsRules(),
			'messages' => $this->messageWithTranslate(),
		];
	}

	/**
	 * Get a validation factory instance.
	 *
	 * @return \Illuminate\Contracts\Validation\Factory
	 */
	protected function getValidationFactory()
	{
		return app(Factory::class);
	}

}
