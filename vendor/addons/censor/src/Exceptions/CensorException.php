<?php

namespace Addons\Censor\Exceptions;

use Lang;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class CensorException extends HttpResponseException {

	public function __construct(Request $request, $validator)
	{
		//if ($request->expectsJson() || $request->offsetExists('of') || (!empty($request->route()) && in_array('api', $request->route()->gatherMiddleware())))

		//OutputResponseFactory exists
		if (class_exists('\Addons\Core\Http\OutputResponseFactory')) {

			$errors = $validator->errors()->toArray();
			$messages = [];
			$message_name = Lang::has('censor.post_fields_invalid') ? 'censor.post_fields_invalid' : 'censor::censor.post_fields_invalid';

			foreach ($errors as $lines) {
				foreach ($lines as $message) {
					$messages[] = trans($message_name.'.list', compact('message'));
				}
			}
			$data = ['errors' => $errors, 'messages' => implode($messages)];

			$this->response = app('Addons\Core\Http\OutputResponseFactory')->make('failure', $message_name, false, $data, true);

		} else { //native 

			$errors = $validator->errors()->getMessages();
			if ($request->expectsJson()) {
				$this->response = response()->json($errors, 422);
			} else {
				$this->response = redirect()->back()->withInput(
					$request->input()
				)->withErrors($errors);
			}
		}
	}

}