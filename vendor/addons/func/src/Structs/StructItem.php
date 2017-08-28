<?php

namespace Addons\Func\Structs;

use LengthException;
use InvalidArgumentException;
use Addons\Func\Structs\Struct;

class StructItem implements \ArrayAccess
{

	protected $name = null;
	protected $type = null;
	private $sizeof = 0;
	protected $length = 1;
	protected $data = null;

	public function __construct($name, $type, $length = 1)
	{
		if (!in_array($type, Struct::defineds))
			throw new InvalidArgumentException('Struct `'.$name.'` type: '. $type . ' is not invalid');
		if (bccomp($length, PHP_INT_MAX) > 0 || $length <= 0)
			throw new InvalidArgumentException('Struct `'.$name.'` length must > 0 && < PHP_INT_MAX.');
		$this->name     = $name;
		$this->type     = $type;
		$this->length   = $length;
		$this->sizeof   = strlen(pack($type, '123456'));
		$this->data     = str_repeat("\x0", $this->size());
	}

	public function type()
	{
		return $this->type;
	}

	public function name()
	{
		return $this->name;
	}

	public function length()
	{
		return $this->length;
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

	public function at($offset, $value = null, $asRaw = false)
	{
		if (!$this->offsetExists($offset))
			return null;

		$size = $this->sizeof();
		$offset = $offset * $size;

		//get
		if (is_null($value))
		{
			$data = substr($this->data, $offset, $size);
			return $asRaw ? $data : unpack($this->type(), $data)[1];
		}

		//set
		if ($asRaw)
		{
			if (strlen($value) < $this->sizeof())
				throw new LengthException('Struct `'.$this->name().'['.$offset.']` raw size must be '.$this->sizeof());
		}
		$bytes = $asRaw ? $value : pack($this->type(), $value);
		for($i = 0; $i < $size; ++$offset)
			$this->data[$i + $offset] = $bytes[$i];

		return $this;
	}

	public function atAsRaw($offset, $value = null)
	{
		return $this->at($offset, $value, true);
	}

	public function data($data = null, $asRaw = false)
	{
		//get
		if (is_null($data))
		{
			if ($asRaw)
				return $this->data;

			$data = unpack($this->type().$this->length(), $this->data());
			if ($this->length() == 1)
				return $data[1];
			else
				return in_array($this->type(), [char, uchar]) ? implode('', $data) : array_values($data);
		}

		//set
		if ($asRaw) //二进制
		{
			if (strlen($data) < $this->size())
				throw new LengthException('Struct \''.$this->name().'\' must be a '.$this->size().' size raw bytes');

			$this->data = substr($data, 0, $this->size());
		}
		else
		{
			if(($this->length() != 1 && !is_array($data)) || ($this->length() > 1 && count($data) != $this->length()))
				throw new LengthException('parameter#0 must be '.$this->length().' length array for \''.$this->name().'\'');

			$_d = array_wrap($data);
			array_unshift($_d, str_repeat($this->type(), $this->length()));
			$this->data = call_user_func_array('pack', $_d);
		}

		return $this;
	}

	public function dataAsRaw($data = null)
	{
		return $this->data($data, true);
	}

}
