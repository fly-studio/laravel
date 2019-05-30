<?php

namespace Addons\Server\Protocols\Http\Responses;

use Closure;
use Symfony\Component\HttpFoundation\File\File;
use Addons\Server\Protocols\Http\Responses\Response;

class FileResponse extends Response {

	protected $file = null;
	protected $offset = null;
	protected $length = null;
	protected $deleteFileAfterSend = false;

	public function __construct(File $file, int $offset = 0, int $length = 0)
	{
		$this->file = $file;
		$this->offset = $offset;
		$this->length = $length;
	}

	/**
	 * If this is set to true, the file will be unlinked after the request is send
	 * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
	 *
	 * @param bool $shouldDelete
	 *
	 * @return $this
	 */
	public function deleteFileAfterSend($shouldDelete = true)
	{
		$this->deleteFileAfterSend = $shouldDelete;

		return $this;
	}

	public function send()
	{
		$this->sendMeta();

		$this->sender->file($this->file->getPathname(), $this->offset, $this->length);

		if ($this->deleteFileAfterSend && file_exists($this->file->getPathname())) {
			unlink($this->file->getPathname());
		}
	}

}
