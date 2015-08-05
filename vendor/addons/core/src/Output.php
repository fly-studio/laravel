<?php
namespace Addons\Core;


class Output {

	public static function json($data)
	{
		return json_encode($data);
	}

	public static function jsonp($data, $callback = 'callback')
	{
		return 'if (typeof '.$jsonp.' == "Function") {'.$jsonp.'.call(this,'.self::json($data).');}';
	}

	public static function script($data)
	{
		return 'var RESULT = '.self::json($data).';if (self != parent) parent.RESULT = self.RESULT;';
	}

	public static function csv($data)
	{
		return csv_encode($data);
	}

	public static function excel($data)
	{
		$excel = new PHPExcel();
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$sheet->fromArray($data);

		$filepath = tempnam(Kohana::$cache_dir.DIRECTORY_SEPARATOR.'util', 'phpexcel_');
		$result = NULL;
		$objWriter = new PHPExcel_Writer_Excel2007($excel);
		$objWriter->save($filepath);
		$result = file_get_contents($filepath);
		@unlink($filepath);
		return $result;
	}

	public static function text($data)
	{
		is_array($data) && $data = var_export($data);
		return $data;
	}

	public static function xml($data)
	{
		return xml_encode($data);
	}

	public static function yaml($data)
	{
		return function_exists('yaml_emit') ? yaml_emit($data) : Spyc::YAMLDump($data);
	}


}