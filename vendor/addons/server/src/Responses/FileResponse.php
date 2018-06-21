<?php

namespace Addons\Server\Responses;
;
use Addons\Server\Contracts\AbstractResponse;

class FileResponse extends AbstractResponse {

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
		$this->sender->file($this->file_path, $this->offset, $this->length);
	}

}
