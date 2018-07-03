<?php

namespace Addons\Server\Routing\Route;

trait ParameterTrait {

	/**
	 * Get the key / value list of parameters without null values.
	 *
	 * @return array
	 */
	public function parametersWithoutNulls(array $parameters)
	{
		return array_filter($parameters, function ($p) {
			return ! is_null($p);
		});
	}

}
