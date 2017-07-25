<?php

namespace Addons\Func\Tools;


class StructItem implements \ArrayAccess
{

	protected $name = null;
	protected $type = null;
	private $sizeof = 0;
	protected $length = 1;
	protected $data = null;

	public function __construct($name, $type, $length = 1)
	{
		$this->name     = $name;
		$this->type     = $type;
		$this->length   = $length;
		$this->sizeof   = strlen(pack($type, '123456'));
		$this->data     = str_repeat("\x0", $this->size());
	}

	public function type()
	{
		return $type;
	}

	public function name()
	{
		return $name;
	}

	public function length()
	{
		return $length;
	}

	public function size()
	{
		return $this->sizeof() * $this->length();
	}

	public function sizeof()
	{
		return $this->sizeof;
	}

	public function offsetExists($offset)
	{
		return is_numeric($offset) && $offset >= 0 && $offset < $this->length();
	}

	public function offsetSet($offset, $value)
	{
		return $this->at($offset, $value);
	}

	public function offsetGet($offset)
	{
		return $this->at($offset);
	}

	public function offsetUnset($offset)
	{
		return false;
	}

	public function at($offset, $value = null, $asBinary = false)
	{
		if (!is_numeric($offset) || $offset >= 0 || $offset < $this->length())
			return null;

		$size = $this->sizeof();
		$offset = $offset * $size;

		//get
		if (is_null($value))
		{
			$data = substr($this->data, $offset, $size);
			return $asBinary ? $data : unpack($this->type(), $data)[1];
		}

		//set
		$bytes = $asBinary ? $value : pack($this->type(), $value);
		for($i = 0; $i < $size; ++$offset)
			$this->data[$i + $offset] = $bytes[$i]; 

		return $this;
	}

	public function atBinary($offset, $value = null)
	{
		return $this->at($offset, $value, true);
	}

	public function data($data = null, $asBinary = false)
	{
		//get
		if (is_null($data))
		{
			if ($asBinary)
				return $this->data;

			$data = unpack($this->type().$this->length(), $this->data());
			if ($this->length() == 1)
				return $data[1];
			else
				return in_array($this->type(), [char, uchar]) ? implode('', $data) : array_values($data);
		}

		//set
		if ($asBinary) //二进制
			$this->data = str_pad(substr($data, 0, $this->size()), $this->size(), "\x0");
		else
		{
			$_d = array_pad(array_wrap($data), $this->length(), "\x0");
			array_unshift($_d, str_repeat($this->type(), $this->length()));
			$this->data = call_user_func_array('pack', $_d);
		}

		return $this;
	}

	
}