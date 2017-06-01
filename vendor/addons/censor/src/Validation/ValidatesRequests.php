<?php
namespace Addons\Censor\Validation;

use Closure;
use Addons\Censor\Factory;
use Addons\Censor\Exceptions\CensorException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests as BaseValidatesRequests;

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
		$censor = $this->getCensorFactory()->make($censorKey, $attributes, $model)->data($request->all());
		$validator = $censor->validator();
		return $validator->fails() ? $this->throwValidationException($request, $validator) : $censor->validData();
	}

	/**
     * Throw the failed validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwValidationException(Request $request, $validator)
    {
		throw new CensorException($request, $validator);
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
