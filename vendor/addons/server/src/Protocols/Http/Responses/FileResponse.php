<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Addons\Server\Protocols\Http\Response;
use Addons\Server\Protocols\Http\InnerResponse;

class FileResponse extends Response {

	protected $file_path = null;
	protected $offset = null;
	protected $length = null;

	public function file(string $file_path, int $offset = 0, int $length = 0)
	{
		$this->file_path = $file_path;
		$this->offset = $offset;
		$this->length = $length;
	}

	public function send(\swoole_http_response $nativeResponse)
	{
		$this->sendMeta($nativeResponse);

		$nativeResponse->sendfile($this->file_path, $this->offset, $this->length);
	}

}
