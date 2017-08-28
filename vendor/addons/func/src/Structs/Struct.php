<?php

namespace Addons\Func\Structs;

define('MACHINE_ENDIAN', 0);
define('LITTLE_ENDIAN', 1 << 0);
define('BIG_ENDIAN', 1 << 1);

use LengthException;
use InvalidArgumentException;
use Addons\Func\Structs\StructItem;

class Struct implements \ArrayAccess
{
	const defineds = [
		'char' => 'c',
		'uchar' => 'C', 'byte' => 'C',
		'short' => 's',
		'ushort' => 'S',
		'ushort_le' => 'v',
		'ushort_be' => 'n',
		'int' => 'l', 'long' => 'l',
		'uint' => 'L', 'ulong' => 'L',
		'uint_le' => 'V', 'ulong_le' => 'V',
		'uint_be' => 'N', 'ulong_be' => 'N',
		'int64' => 'q', 'longlong' => 'q',
		'uint64' => 'Q', 'ulonglong' => 'Q',
		'uint64_le' => 'P', 'ulonglong_le' => 'P',
		'uint64_be' => 'J', 'ulonglong_be' => 'J',
		'float' => 'f',
		'double' => 'd',
	];

	const endianesses = [
		'ushort', 'uint', 'ulong', 'uint64', 'ulonglong'
	];

	protected $items = [];
	protected $structs = [];
	protected $endianess;
	protected $size = 0;

	public static function machineEndianess()
	{
		$val  = 1234;
		$test = pack(uint, $val);
		$big  = array_sum(unpack('N', $test)); // change to uint_be
		return ($val == $big) ? BIG_ENDIAN : LITTLE_ENDIAN;
	}

	public function __construct(array $structs, $endianess = MACHINE_ENDIAN)
	{
		$this->parseStructs($structs, $endianess);
	}

	private function parseStructs($structs, $endianess)
	{
		if (empty($structs))
			throw new InvalidArgumentException('Empty structs.');
		$this->endianess = $endianess;
		$this->items = [];
		$this->structs = [];
		$this->size = 0;
		$pattern = '#^(?<type>'.implode('|', array_keys(static::defineds)).')(\[(?<length>\d*)\])?$#i';
		foreach($structs as $name => $item)
		{
			if (!preg_match($pattern, $item, $matches))
				throw new InvalidArgumentException('Struct \''.$name.': '.$item.'\' is invalid.');

			if (isset($matches['length']) && (empty($matches['length']) || bccomp($matches['length'], PHP_INT_MAX) > 0))
				throw new InvalidArgumentException('Struct \''.$name.': '.$item.'\' , length must be > 0 && < PHP_INT_MAX.');

			if (($endianess & LITTLE_ENDIAN) == LITTLE_ENDIAN)
				$type = $this->convertToLE($matches['type']);
			else if (($endianess & BIG_ENDIAN) == BIG_ENDIAN)
				$type = $this->convertToBE($matches['type']);
			else
				$type = $matches['type'];

			$this->items[$name] = new StructItem($name, static::defineds[ $type ], empty($matches['length']) ? 1 : $matches['length']);
			$this->structs[$name] = [
				'expression' => $item,
				'type' => $matches['type'],
				'length' => $matches['length'],
				'endianess' => $endianess,
				'realType' => $type,
				'pack' => static::defineds[ $type ],
				'size' => $this->items[$name]->size(),
			];
			$this->size += $this->structs[$name]['size'];
		}
	}

	public function convertToBE($type)
	{
		return in_array($type, static::endianesses) ? $type.'_be' : $type;
	}

	public function convertToLE($type)
	{
		return in_array($type, static::endianesses) ? $type.'_le' : $type;
	}

	public function item($offset)
	{
		return $this->offsetExists($offset) ? $this->item[$offset] : null;
	}

	public function endianess()
	{
		return $this->endianess;
	}

	public function items()
	{
		return $this->items;
	}

	public function size()
	{
		return $this->size;
	}

	public function sizeof($offset = null)
	{
		return is_null($offset) ? $this->size() : ($this->offsetExists($offset) ? $this->item($offset)->size() : null);
	}

	public function typeAt($offset)
	{
		return $this->offsetExists($offset) ? $this->structs[$offset]['expression'] : null;
	}

	public function structAt($offset)
	{
		return $this->offsetExists($offset) ? $this->structs[$offset] : null;
	}

	public function at($offset, $value = null, $asRaw = false)
	{
		if (!$this->offsetExists($offset)) return null;

		// get
		if (is_null($value))
			return $this->items[$offset]->data($value, $asRaw);

		// set
		$this->items[$offset]->data($value, $asRaw);
		return $this;
	}

	public function atAsRaw($offset, $value = null)
	{
		return $this->at($offset, $value, true);
	}

	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->items);
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

	public function load($rawData)
	{
		if (strlen($rawData) < $this->size())
			throw new LengthException('parameter#0 must be a '. $this->size(). ' size raw bytes.');

		$offset = 0;
		foreach($this->items as $item)
		{
			$item->dataAsRaw(substr($rawData, $offset, $item->size()));
			$offset += $item->size();
		}
	}

	public function data()
	{
		$data = '';
		foreach($this->items as $item)
			$data .= $item->dataAsRaw();
		return $data;
	}
}
