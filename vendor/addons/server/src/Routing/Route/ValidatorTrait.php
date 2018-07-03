<?php

namespace Addons\Server\Routing\Route;

use Addons\Server\Routing\Matching\RawValidator;
use Addons\Server\Routing\Matching\RegexValidator;
use Addons\Server\Routing\Matching\CallableValidator;
use Addons\Server\Routing\Matching\RemoteIpValidator;
use Addons\Server\Routing\Matching\ServerPortValidator;
use Addons\Server\Routing\Matching\ServerProtocolValidator;
use Addons\Server\Routing\Matching\CaptureProtocolValidator;

trait ValidatorTrait {

	/**
	 * The validators used by the routes.
	 *
	 * @var array
	 */
	public static $validators;

	/**
	 * Get the route validators for the instance.
	 *
	 * @return array
	 */
	public static function getValidators()
	{
		if (isset(static::$validators)) {
			return static::$validators;
		}

		// To match the route, we will use a chain of responsibility pattern with the
		// validator implementations. We will spin through each one making sure it
		// passes and then we will know if the route as a whole matches request.
		return static::$validators = [
			new CaptureProtocolValidator, new RemoteIpValidator,
			new ServerPortValidator, new ServerProtocolValidator,
			new RawValidator, new RegexValidator,
			new CallableValidator
		];
	}

}
