<?php

namespace Addons\Censor\Exceptions;

use Lang;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CensorException extends HttpResponseException {

	public function __construct(array $data, Validator $validator)
	{
		//Output\ResponseFactory exists
		if (class_exists('\Addons\Core\Http\Output\ResponseFactory')) {

			$errors = $validator->errors()->toArray();

			$this->response = app('\Addons\Core\Http\Output\ResponseFactory')->error()->code(422)->rawMessage($errors);

		} else { //native

			$errors = $validator->errors()->getMessages();

			$this->response = redirect()->back()->withInput($data)->withErrors($errors);
		}
	}

}
