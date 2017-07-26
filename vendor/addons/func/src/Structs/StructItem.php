<?php

namespace Addons\Func\Structs;

use Addons\Func\Structs\Struct;
use Addons\Func\Exceptions\Structs\TypeException;
use Addons\Func\Exceptions\Structs\SizeException;

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
			throw new TypeException('Struct `'.$name.'` type: '. $type . ' is not invalid');
		if (bccomp($length, PHP_INT_MAX) > 0 || $length <= 0)
			throw new TypeException('Struct `'.$name.'` length must > 0 && < PHP_INT_MAX.');
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

	public function at($offset, $value = null, $asBinary = false)
	{
		if (!$this->offsetExists($offset))
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
		if ($asBinary)
		{
			if (strlen($value) < $this->sizeof())
				throw new SizeException('Struct `'.$this->name().'['.$offset.']` binary size must be '.$this->sizeof());
		}
		$bytes = $asBinary ? $value : pack($this->type(), $value);
		for($i = 0; $i < $size; ++$offset)
			$this->data[$i + $offset] = $bytes[$i]; 

		return $this;
	}

	public function atAsBinary($offset, $value = null)
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
		{
			if (strlen($data) < $this->size())
				throw new SizeException('Struct \''.$this->name().'\' must be a '.$this->size().' size binary bytes');

			$this->data = substr($data, 0, $this->size());
		}
		else
		{
			if(($this->length() != 1 && !is_array($data)) || ($this->length() > 1 && count($data) != $this->length()))
				throw new SizeException('parameter#0 must be '.$this->length().' length array for \''.$this->name().'\'');

			$_d = array_wrap($data);
			array_unshift($_d, str_repeat($this->type(), $this->length()));
			$this->data = call_user_func_array('pack', $_d);
		}

		return $this;
	}

	public function dataAsBinary($data = null)
	{
		return $this->data($data, true);
	}

}