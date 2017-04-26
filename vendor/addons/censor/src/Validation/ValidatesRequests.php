<?php
namespace Addons\Censor\Validation;

use Closure;
use Addons\Sensor\Factory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests as BaseValidatesRequests;
use Lang;

trait ValidatesRequests
{
	use BaseValidatesRequests;

	public function getScriptValidate($table, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($table, $attributes, $model);

		return $censor->js();
	}
	/**
	 * [autoValidate description]
	 * @param  Request $request [description]
	 * @param  [type]  $table   [description]
	 * @param  string  $attributes    [description]
	 * @return [type]           [description]
	 */
	public function autoValidate(Request $request, $table, $attributes, Model $model = null)
	{
		if ($request->expectsJson() || $request->offsetExists('of'))
			return $this->validateWithApi($request, $table, $attributes, $model);
		else 
			return $this->validateWithBack($request, $table, $attributes, $model);
	}

	public function validateWithBack(Request $request, $table, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($table, $attributes, $model)->data($request->all());
		$validator = $censor->validator();
		return $validator->fails() ? $this->throwValidationException($request, $validator) : $censor->validData();
	}

	/**
	 * [validateWithApi description]
	 * @param  Request $request [description]
	 * @param  [type]  $table   [description]
	 * @param  string  $attributes    [description]
	 * @return [type]           [description]
	 */
	public function validateWithApi(Request $request, $table, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($table, $attributes, $model)->data($request->all());
		$validator = $censor->validator();
		return $validator->fails() ? $this->throwValidationOutputResponse($validator->errors()) : $this->validData();
	}

	private function throwValidationOutputResponse(\Illuminate\Support\MessageBag $messagebag)
	{
		$errors = $messagebag->toArray();
		$messages = [];
		foreach ($errors as $lines) {
			foreach ($lines as $message) {
				$messages[] = trans(Lang::has('validation.post_fields_invalid.list') ? 'validation.post_fields_invalid.list' : 'core::common.validation.post_fields_invalid.list', compact('message'));
			}
		}
		return $this->failure_post(false, ['errors' => $errors, 'messages' => implode($messages)], true);
	}

	/**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getCensorFactory()
    {
        return app(Factory::class);
    }
}
