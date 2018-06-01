<?php

namespace Addons\Server\Contracts;

use Addons\Func\Contracts\BootTrait;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractService;

abstract class AbstractRequest {

	use BootTrait;

	protected $options;
	protected $service;
	protected $data;

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

}
