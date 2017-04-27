<?php
namespace Addons\Censor\Validation;

use Closure;
use Addons\Censor\Factory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests as BaseValidatesRequests;
use Lang;

trait ValidatesRequests
{
	use BaseValidatesRequests;

	public function getValidatorScript($censorKey, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($censorKey, $attributes, $model);

		return $censor->js();
	}
	/**
	 * if json or api, use validateWithApi.
	 * otherwish use validateWithNative
	 * 
	 * @param  Request $request
	 * @param  string  $censorKey
	 * @param  array  $attributes
	 * @param  Model|null $model
	 * @return array|Exception
	 */
	public function autoValidate(Request $request, $censorKey, $attributes, Model $model = null)
	{
		if ($request->expectsJson() || $request->offsetExists('of') || (!empty($request->route()) && in_array('api', $request->route()->gatherMiddleware())))
			return $this->validateWithApi($request, $censorKey, $attributes, $model);
		else 
			return $this->validateWithNative($request, $censorKey, $attributes, $model);
	}

	/**
	 * laravel's native validator
	 * flash and back page when fails
	 * 
	 * @param  Request    $request
	 * @param  string     $censorKey
	 * @param  array      $attributes
	 * @param  Model|null $model
	 * @return array|Exception
	 */
	public function validateWithNative(Request $request, $censorKey, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($censorKey, $attributes, $model)->data($request->all());
		$validator = $censor->validator();
		return $validator->fails() ? $this->throwValidationException($request, $validator) : $censor->validData();
	}

	/**
	 * api's validator
	 * OutputException when fails
	 * 
	 * @param  Request    $request
	 * @param  string     $censorKey
	 * @param  array      $attributes
	 * @param  Model|null $model
	 * @return array|Exception
	 */
	public function validateWithApi(Request $request, $censorKey, $attributes, Model $model = null)
	{
		$censor = $this->getCensorFactory()->make($censorKey, $attributes, $model)->data($request->all());
		$validator = $censor->validator();
		return $validator->fails() ? $this->throwValidationOutputResponse($validator->errors()) : $censor->validData();
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
     * Get a censor factory instance.
     *
     * @return \Addons\Censor\Factory
     */
    protected function getCensorFactory()
    {
        return app(Factory::class);
    }
}
