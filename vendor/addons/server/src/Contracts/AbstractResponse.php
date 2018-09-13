<?php

namespace Addons\Server\Contracts;

use Closure;
use RuntimeException;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ConnectBinder;
use Addons\Func\Contracts\TraitsBootTrait;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractRequest;

abstract class AbstractResponse {

	use TraitsBootTrait;

	protected $binder;
	protected $sender;
	protected $content = null;

	public function __construct($content = null)
	{
		$this->content = $content;
	}

	public static function build(...$args)
	{
		return new static(...$args);
	}

	public function with(ConnectBinder $binder, AbstractSender $sender)
	{
		$this->binder = $binder;
		$this->sender = $sender;
	}

	public function options(): ServerOptions
	{
		return $this->binder->options();
	}

	public function binder(): ConnectBinder
	{
		return $this->binder;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function prepare(AbstractRequest $request)
	{
		return $this;
	}

	abstract protected function send();

}
