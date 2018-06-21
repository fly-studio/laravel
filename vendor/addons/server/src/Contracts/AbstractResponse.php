<?php

namespace Addons\Server\Contracts;

use Closure;
use RuntimeException;
use Addons\Server\Structs\ServerOptions;
use Addons\Func\Contracts\TraitsBootTrait;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractRequest;

abstract class AbstractResponse {

	use TraitsBootTrait;

	protected $options;
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

	public function with(ServerOptions $options, AbstractSender $sender)
	{
		$this->options = $options;
		$this->sender = $sender;
	}

	public function options()
	{
		return $this->options;
	}

	public function sender()
	{
		return $this->sender;
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
