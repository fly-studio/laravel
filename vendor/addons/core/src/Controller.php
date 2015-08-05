<?php
namespace Addons\Core;


use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Addons\Core\Output;
use Addons\Smarty\View\Engine;

class Controller extends BaseController {

	protected function error($message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		//$this->response->status(500);
		$result = $this->_make_output('error',$message_name,$url,$data,$export_data);
		return $result;
	}

	protected function success($message_name = NULL, $url = TRUE, array $data = [], $export_data = TRUE)
	{
		$result = $this->_make_output('success',$message_name,$url,$data,$export_data);
		return $result;
	}

	protected function failure($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		$result = $this->_make_output('failure',$message_name,$url,$data,$export_data);
		return $result;
	}

	protected function _make_output($type, $message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		
		$msg = $message_name;
		if (!is_array($message_name))
		{
			$msg = !empty($message_name) ? trans('common.'.$message_name) : []; $msg == $message_name && $msg = [];
			$default = trans('Core::common.default.'.$type );
			$msg = _extends( $msg, $default); //填充

			foreach ($msg as $key => $value) 
				$msg[$key] = empty($data) ?  $value : trans($value, array_keyflatten($data, '/', ':')); //转化成有意义的文字
		}
		
		$msg = array_keyfilter($msg, 'title,content');
		$result = array(
			'result' => $type,
			'timeline' => time(),
			'run-time' => microtime(TRUE) - LARAVEL_START,
			'uid' => 0,
			'message' => $msg,
			'url' => $url,
			'data' => $export_data ? $data : [],
		);
		return $this->output($result);
	}

	protected function output(array $data)
	{
		$request = app('request');
		$charset = app('config')['app']['charset'];
		$of = strtolower($request->input('of'));
		$jsonp = $request->input('jsonp'); 
		empty($jsonp) && $jsonp = $request->input('callback');
		$response = new Response();
		$types = ['xml' => 'text/xml', 'yaml' => 'application/yaml', 'json' => 'application/json', 'jsonp' => 'application/x-javascript', 'script' => 'application/x-javascript', 'csv' => 'application/vnd.ms-excel', 'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text' => 'text/plain', 'html' => 'text/html'];
		if (!array_key_exists($of, $types))
		{
			if ($request->ajax())
				$of = empty($jsonp) ? 'json' : 'jsonp';
			else
				$of = 'html';
		}
		$response->header('Content-Type', $types[$of].'; charset='.$charset);
		$content = $of != 'html' ? Output::$of($data, $jsonp) : view('tips', ['_data' => $data]);
		return $response->setContent($content);
	}

}