<?php

namespace Addons\Core\Validation;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Foundation\Validation\ValidatesRequests as BaseValidatesRequests;

trait ValidatesRequests
{
	use BaseValidatesRequests {
		validate as scrapValidate;
	}
	/**
	 * The default error bag.
	 *
	 * @var string
	 */
	
	private function getValidationData($table, $keys)
	{
		$config = app('config')->get('validation.'.$table);
		$keys == '*' && $keys = array_keys($config);
		$validation_data = array_keyfilter($config, $keys);
		$rules = $messages = $attributes = [];
		array_walk($validation_data, function($v, $k) use(&$rules, &$messages, &$attributes){
			isset($v['rules']) && $rules[$k] = $v['rules'];
			isset($v['message']) && $messages[$k] = $v['message'];
			isset($v['name']) && $attributes[$k] = $v['name'];
		});

		return compact('rules', 'messages', 'attributes');
	}
	/**
	 * [validate description]
	 * @param  Request $request [description]
	 * @param  [type]  $table   [description]
	 * @param  string  $keys    [description]
	 * @return [type]           [description]
	 */
	public function validate(Request $request, $table, $keys = '*')
	{
		$validateData = $this->getValidationData($table, $keys);
		$validator = $this->getValidationFactory()->make($request->all(), $validateData['rules'], array_keyflatten($validateData['messages'],'.'), $validateData['attributes']);
		return $validator;
	}

	public function getScriptValidate($table, $keys = '*')
	{
		$validateData = $this->getValidationData($table, $keys);
		$validator = $this->getValidationFactory()->make([], $validateData['rules'], $validateData['messages'], $validateData['attributes']);
		$rules = $validator->getjQueryRules();

		return ['rules' => $rules, 'messages' => $validateData['messages']];
	}
	/**
	 * [autoValidate description]
	 * @param  Request $request [description]
	 * @param  [type]  $table   [description]
	 * @param  string  $keys    [description]
	 * @return [type]           [description]
	 */
	public function autoValidate(Request $request, $table, $keys = '*')
	{
		if ($request->ajax()) return $this->tipsValidate($request, $table, $keys);

		$validator = $this->validate($request, $table, $keys);
		if ($validator->fails())
			$this->throwValidationException($request, $validator);

		return $this->filterValidatorData($validator, $keys);;
	}
	/**
	 * [tipsValidate description]
	 * @param  Request $request [description]
	 * @param  [type]  $table   [description]
	 * @param  string  $keys    [description]
	 * @return [type]           [description]
	 */
	public function tipsValidate(Request $request, $table, $keys = '*')
	{
		$validator = $this->validate($request, $table, $keys);
		if ($validator->fails()) {
			$request->flashExcept('password');
			throw new HttpResponseException($this->failure_validate($validator->errors()));
		}
		return $this->filterValidatorData($validator, $keys);
	}
	/**
	 * [filterValidatorData description]
	 * @param  Validator $validator [description]
	 * @param  [type]    $keys      [description]
	 * @return [type]               [description]
	 */
	private function filterValidatorData(Validator $validator, $keys)
	{
		$data = $validator->getData();
		return $keys == '*' ? $data : array_keyfilter($data, $keys);
	}
}
