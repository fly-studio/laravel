<?php

namespace Addons\Censor\Exceptions;

use Lang;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class CensorException extends HttpResponseException {

	public function __construct(Request $request, $validator)
	{
		//Output\ResponseFactory exists
		if (class_exists('\Addons\Core\Http\Output\ResponseFactory')) {

			$errors = $validator->errors()->toArray();

			$this->response = app('\Addons\Core\Http\Output\ResponseFactory')->error()->code(422)->rawMessage($errors);

		} else { //native

			$errors = $validator->errors()->getMessages();

			if ($request->expectsJson())
			{
				$this->response = response()->json($errors, 422);
			}
			else
			{
				$this->response = redirect()->back()->withInput(
					$request->input()
				)->withErrors($errors);
			}
		}
	}

}
