<?php

namespace Addons\Func\Tools;

define('char', 'c');
define('uchar', 'C');
define('byte', 'C');
define('short', 's');
define('ushort', 'S');
define('int', 'l');
define('uint', 'L');
define('int64', 'q');
define('uint64', 'Q');
define('ushort_le', 'v');
define('ushort_be', 'n');
define('uint_le', 'V');
define('uint_be', 'N');
define('uint64_le', 'P');
define('uint64_be', 'J');
define("float", "f");
define("double", "d");

define('LITTLE_ENDIAN', 1 << 5);
define('BIG_ENDIAN', 1 << 6);



class Struct extends \ArrayAccess
{

	public static function machineEndianess()
	{
		$val  = 1234;
		$test = pack(uint, $val);
		$big  = array_sum(unpack(uint_be, $test));
		return ($val == $big) ? BIG_ENDIAN : LITTLE_ENDIAN;
	}


	public $members = array();
	public function __construct()
	{
		$argc = func_num_args();
		$argv = func_get_args();
		/* Parse the vararg list */
		for ($i = 0; $i < $argc;) {
			if (!is_string(($name = $argv[$i++]))) {
				//name
				throw new ArgException("Invalid Arguments: Expected member name, got '" . $name . "'");
			}
			//print(">> NAME: ".$name.PHP_EOL);
			if ($i >= $argc) {
				throw new ArgException("Missing type for '%s'", $name);
			}
			if (!is_object(($type = $argv[$i++])) && !$type instanceof Struct) {
				if (!is_string($type) || strlen($type) != 1) {
					//type
					throw new ArgException("Invalid Arguments: Expected member type, got '" . $type . "'");
				}
				if (!StructMember::typeValid($type)) {
					throw new ArgException("Invalid type %c", $type);
				}
			}
			//print(">> TYPE: ".$type.PHP_EOL);
			if ($i >= $argc || !is_numeric(($count = $argv[$i]))) {
				//count
				$this->members[$name] = new StructMember(
					$name, $type
				);
				continue;
			}
			$i++;
			//print(">> COUNT: ".$count.PHP_EOL);
			if ($i >= $argc || !is_numeric(($flags = $argv[$i]))) {
				//endianess
				$this->members[$name] = new StructMember(
					$name, $type, $count
				);
				continue;
			}
			$i++;
			//print(">> FLAGS: ".$flags.PHP_EOL);
			$this->members[$name] = new StructMember(
				$name, $type, $count, $flags
			);
		}
	}
	public function getPackString()
	{
		$str = '';
		foreach ($this->members as $memb) {
			$str .= $memb->type();
		}
		return $str;
	}

	public function getSize()
	{
		$sz = 0;
		foreach ($this->members as $memb) {
			$sz += $memb->getSize();
		}
		return $sz;
	}
	public function __clone()
	{
		foreach ($this->members as $name => &$member) {
			$this->members[$name] = clone ($member);
		}
	}
	public function getData()
	{
		$data = '';
		foreach ($this->members as $name => $member) {
			if ($member->isSubStruct()) {
				foreach ($member->getValue() as $subStruct) {
					$data .= $subStruct->getData();
				}
			} else {
				$data .= $member->data;
			}
		}
		return $data;
	}
	/* Reads binary data $data into the members of the struct */
	public function apply($data)
	{
		$i = 0;
		foreach ($this->members as $name => &$memb) {
			/* If we have a substruct, recursively call apply on it */
			if ($memb->isSubStruct()) {
				/* Create an array of structures as the binary data */
				$memb->data = array();
				for ($i = 0; $i < $memb->length(); $i++) {
					/* Clone the structure and its members */
					$subStruct = clone ($memb->type());
					/* Fill it */
					$data = $subStruct->apply($data);
					/* Insert it */
					$memb->data[$i] = $subStruct;
					//printf("Substruct insert -> %s[%d]\n", $memb->name, $i);
					/*foreach($subStruct->members as $n => $m){
				printf("%s => 0x%x\n", $n, $m->getValue());
				//var_dump($m);
				}*/
				}
			} else {
				/* Read the binary data represented by the member */
				$totalSz   = $memb->sizeof() * $memb->length();
				$memb_data = substr($data, 0, $totalSz);
				/* Assign the read data and increment position */
				$memb->data = $memb_data;
				$data       = substr($data, $totalSz);
				/* Handle dynamic strings */
				if (($memb->getFlags() & FLAG_STRSZ) == FLAG_STRSZ) {
					/* Update type and size  of the next member
					depending on the value we just read */
					$nextStr = &array_values($this->members)[$i + 1];
					$nextStr->setType(char);
					$nextStr->setCount($memb->getValue());
				}
			}
			$i++;
		}
		/* Return the remaining data */
		return $data;
	}
	public static function isStructArray($arr)
	{
		return is_array($arr) && count($arr) > 0 && $arr[0] instanceof Struct;
	}
	public static function printStruct($struct)
	{
		foreach ($struct->members as $name => $memb) {
			$value = $memb->getValue();
			if (is_array($value)) {
				if (count($value) > 0 && $value[0] instanceof Struct) {
					foreach ($value as $subStruct) {
						self::printStruct($subStruct);
					}
				} else {
					printf("%s\n", $name);
					var_dump($value);
				}
			} else {
				printf("%s => 0x%x\n", $name, $value);
			}
		}
	}
}
