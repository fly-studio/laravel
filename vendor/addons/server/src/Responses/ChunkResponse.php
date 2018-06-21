<?php

namespace Addons\Server\Responses;

use Addons\Server\Contracts\AbstractResponse;

class ChunkResponse extends AbstractResponse {

	protected $callback = null;

	public function __construct($content = null, callable $callback = null)
	{
		$this->content = $content;
		$this->callback = $callback;
	}

	public function send()
	{
		if(!empty($this->content) || is_numeric($this->content))
			$this->sender->chunk($this->content);

		if (is_callable($this->callback))
			call_user_func($this->callback, $this->sender);

		$this->sender->end();
	}

}
