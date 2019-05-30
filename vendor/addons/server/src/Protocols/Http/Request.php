<?php

namespace Addons\Server\Protocols\Http;

use BadMethodCallException;
use Addons\Server\Contracts\AbstractRequest;
use Illuminate\Http\Request as LaravelRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends AbstractRequest {

	protected $swooleRequest;
	protected $swooleResponse;
	protected $laravelRequest;

	public function __construct($request, $response)
	{
		$this->swooleRequest = $request;
		$this->swooleResponse = $response;
	}

	public function keywords(): string
	{
		return $this->request->server['request_uri'];
	}

	public function raw(): string
	{
		return $this->request->rawContent();
	}

	public function getLaravelRequest()
	{
		if (empty($this->laravelRequest))
		{
			LaravelRequest::enableHttpMethodParameterOverride();

			$server = [];
			foreach ($this->swooleRequest->server as $key => $value) {
				$server[strtoupper($key)] = $value;
			}
			foreach ($this->swooleRequest->header as $key => $value) {
				$server['HTTP_'.str_replace('-', '_', strtoupper($key))] = $value;
			}

			$symfonyRequest = call_class_method(new SymfonyRequest(), 'createRequestFromFactory', $this->swooleRequest->get ?? [], $this->swooleRequest->post ?? [], [], $this->swooleRequest->cookie ?? [], $this->swooleRequest->files ?? [], $server, $this->swooleRequest->rawContent());

			if (0 === strpos($symfonyRequest->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
				&& \in_array(strtoupper($symfonyRequest->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
			) {
				parse_str($symfonyRequest->getContent(), $data);
				$symfonyRequest->request = new ParameterBag($data);
			}

			$this->laravelRequest = LaravelRequest::createFromBase($symfonyRequest);
		}

		return $this->laravelRequest;
	}

	public function __call($name, $arguments)
	{
		$laravelRequest = $this->getLaravelRequest();

		if (method_exists($laravelRequest, method_name))
			return call_user_func([$laravelRequest, $name], $arguments);

		throw new BadMethodCallException(sprintf(
			'Method %s::%s does not exist.', static::class, $method
		));
	}
}
