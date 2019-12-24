<?php

namespace Addons\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Addons\Core\Tools\OutputEncrypt;

class DecryptPost
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (in_array(strtoupper($request->method()), ['POST', 'PUT', 'DELETE']) && $request->headers->has('X-RSA'))
		{
			$rsa = urldecode($request->header('X-RSA'));
			$encrypted = $request->input('encrypted');
			$data = $request->input('data');

			if (!empty($encrypted) && !empty($data) && !empty($rsa))
			{
				$e = new OutputEncrypt($rsa);

				$json = $e->decodeByPublic($data, $encrypted);

				// object json
				if (!empty($json) && $json[0] === '{')
				{
					$request->merge(json_decode($json, true));
					$request->offsetUnset('encrypted');
					$request->offsetUnset('data');
				}
			}
		}

		return $next($request);
	}
}
