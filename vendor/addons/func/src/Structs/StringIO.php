<?php

namespace Addons\Func\Structs;

use OverflowException;
use UnderflowException;
use OutOfBoundsException;

class StringIO {

	protected $data = '';
	protected $offset = 0;
	protected $length = 0;

	public function __construct($data = null)
	{
		!is_null($data) && $this->load($data);
	}

	public function empty()
	{
		$this->load('');
	}

	public function load($data)
	{
		$this->data = $data;
		$this->offset = 0;
		$this->length = strlen($data);
	}

	/**
	 * Move String Pointer
	 *
	 * if $this->offset > $this->length, writing will padding enough '0' to new $this->offset
	 * if $this->offset > $this->length, reading will fail
	 *
	 * @param  [type] $offset [description]
	 * @param  [type] $whence [description]
	 * @return [type]         [description]
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		$_offset = $this->offset;
		switch ($whence) {
			case SEEK_SET:
				$_offset = $offset;
				break;
			case SEEK_CUR:
				$_offset += $offset;
				break;
			case SEEK_END:
				$_offset = $this->length + $offset;
				break;
		}
		if ($_offset < 0) // it can add size when writing
			throw new UnderflowException('Offset < 0, out of string.');

		$this->offset = $_offset;
		return $this->offset;
	}

	public function offset()
	{
		return $this->offset;
	}

	public function tell()
	{
		return $this->offset;
	}

	public function rewind()
	{
		return $this->seek(0);
	}

	public function data()
	{
		return $this->data;
	}

	public function length()
	{
		return $this->length;
	}

	public function write($data, $offset = null)
	{
		if (!is_null($offset)) $this->seek($offset);

		$newSize = $this->offset + strlen($data);
		$length = strlen($data);

		//you can set the offset > length
		//then resize the str and fill 0
		if ($newSize > $this->length)
		{
			$this->data = str_pad($this->data, $newSize, "\x00");
			$this->length = $newSize;
		}

		$this->data = substr_replace($this->data, $data, $this->offset, $length);
		$this->seek($length, SEEK_CUR);
		return $length;
	}

	public function read($length, $offset = null)
	{
		if (!is_null($offset)) $this->seek($offset);

		if ($this->offset >= $this->length)
			throw new OutOfBoundsException('Cannot read, reach EOF of string.', 1);

		$str = substr($this->data, $this->offset, $length);

		if (empty($length))
			$this->seek(0, SEEK_END);
		else if (strlen($str) != $length)
			throw new OverflowException('Read string overflow.');
		else
			$this->seek($length, SEEK_CUR);

		return $str;
	}

}
