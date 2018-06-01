<?php

namespace Addons\Server\Contracts;

use Addons\Func\Contracts\BootTrait;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractService;

abstract class AbstractResponse {

	use BootTrait;

	protected $options;
	protected $service;
	protected $data;
	protected $nextAction;

	public function __construct(ServerOptions $options, ?AbstractService $service, ?string $data)
	{
		$this->options = $options;
		$this->service = $service;
		$this->data = $data;

		$this->boot();
	}

	public static function build(...$args)
	{
		return new static(...$args);
	}

	public static function buildFromRequest(AbstractRequest $request)
	{
		return static::build($request->options(), $request->service(), $request->data());
	}

	public function options()
	{
		return $this->options;
	}

	public function server()
	{
		return $this->options->server();
	}

	public function data()
	{
		return $this->data;
	}

	public function service()
	{
		return $this->service;
	}

	public function nextAction(Closure $callback = null)
	{
		if (is_null($callback)) return $this->nextAction;

		$this->nextAction = $callback;
		return $this;
	}

	abstract public function reply(): ?array;

}
