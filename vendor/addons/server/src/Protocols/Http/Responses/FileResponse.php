<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Addons\Server\Protocols\Http\Responses\Response;

class FileResponse extends Response {

	protected $file_path = null;
	protected $offset = null;
	protected $length = null;

	public function __construct(string $file_path, int $offset = 0, int $length = 0)
	{
		$this->file_path = $file_path;
		$this->offset = $offset;
		$this->length = $length;
	}

	public function send()
	{
		$this->sendMeta();

		$this->sender->file($this->file_path, $this->offset, $this->length);
	}

}