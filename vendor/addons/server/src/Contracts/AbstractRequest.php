<?php

namespace Addons\Server\Contracts;

use Closure;
use Addons\Func\Contracts\BootTrait;
use Addons\Server\Structs\ServerOptions;

abstract class AbstractRequest {

	use BootTrait;

	protected $options;
	protected $raw;

	protected $routeResolver;
	protected $userResolver;

	public function __construct(ServerOptions $options, ?string $raw)
	{
		$this->options = $options;

		$this->raw = $raw;

		$this->boot();
	}

	public static function build(...$args)
	{
		return new static(...$args);
	}

	abstract public function eigenvalue(): string;

	public function raw()
	{
		return $this->raw;
	}

	public function options()
	{
		return $this->options;
	}

	/**
	 * Get the route resolver callback.
	 *
	 * @return \Closure
	 */
	public function getRouteResolver()
	{
		return $this->routeResolver ?: function () {
			//
		};
	}

	public function setRouteResolver(Closure $callback)
	{
		$this->routeResolver = $callback;
		return $this;
	}

	/**
     * Get the user making the request.
     *
     * @param  string|null  $guard
     * @return mixed
     */
    public function user($guard = null)
    {
        return call_user_func($this->getUserResolver(), $guard);
    }

	/**
	 * Get the user resolver callback.
	 *
	 * @return \Closure
	 */
	public function getUserResolver()
	{
		return $this->userResolver ?: function () {
			//
		};
	}

	/**
	 * Set the user resolver callback.
	 *
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function setUserResolver(Closure $callback)
	{
		$this->userResolver = $callback;

		return $this;
	}

}
