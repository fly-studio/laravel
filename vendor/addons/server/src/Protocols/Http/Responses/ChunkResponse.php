<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Addons\Server\Protocols\Http\Responses\Response;

class ChunkResponse extends Response {

	protected $callback = null;

	public function __construct(callable $callback = null)
	{
		$this->callback = $callback;
	}

	public function send()
	{
		$this->sendMeta();

		if(!empty($this->content) || is_numeric($this->content))
			$this->sender->chunk($this->content);

		if (is_callable($this->callback))
			call_user_func($this->callback, $this->sender);

		$this->sender->end();
	}

}
