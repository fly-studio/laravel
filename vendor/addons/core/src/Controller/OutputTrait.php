<?php
namespace Addons\Core\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Addons\Core\Output;
use Addons\Smarty\View\Engine;

trait OutputTrait{


	protected function error($message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		$result = $this->_make_output('error', $message_name, $url, $data, $export_data);
		return $result;
	}

	protected function success($message_name = NULL, $url = TRUE, array $data = [], $export_data = TRUE)
	{
		$result = $this->_make_output('success', $message_name, $url, $data, $export_data);
		return $result;
	}

	protected function failure($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		$result = $this->_make_output('failure', $message_name, $url, $data, $export_data);
		return $result;
	}

	protected function success_login($url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->success(Lang::has('auth.success') ? 'auth.success' :'core::common.default.success_login', $url, $data, $export_data);
	}

	protected function failure_login($url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->failure(Lang::has('auth.failure') ? 'auth.failure' :'core::common.default.failure_login', $url, $data, $export_data);
	}

	protected function failure_validate(\Illuminate\Support\MessageBag $messagebag)
	{
		$errors = $messagebag->toArray();
		$messages = [];
		foreach ($errors as $lines) {
			foreach ($lines as $message) {
				$messages[] = trans('core::common.default.failure_validate.list', compact('message'));
			}
		}
		return $this->_make_output('failure', 'core::common.default.failure_validate', FALSE, ['errors' => $errors, 'messages' => implode($messages)], TRUE);
	}

	protected function _make_output($type, $message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		$msg = $message_name;
		if (!is_array($message_name))
		{
			$msg = Lang::has($message_name) ? trans($message_name) : [];
			is_string($msg) && $msg = ['content' => $msg];
			$default = trans('core::common.default.'.$type );
			$msg = _extends($msg, $default); //填充

			foreach ($msg as $key => $value) 
				$msg[$key] = empty($data) ?  $value : __($value, $data); //转化成有意义的文字
		}
		
		$msg = array_keyfilter($msg, 'title,content');
		$result = array(
			'result' => $type,
			'timeline' => time(),
			'run-time' => microtime(TRUE) - LARAVEL_START,
			'uid' => 0,
			'message' => $msg,
			'url' => is_string($url) ? url($url) : $url,
			'data' => $export_data ? $data : [],
		);
		return $this->output($result);
	}

	protected function output(array $data, $filename = NULL)
	{
		$request = app('request');
		$charset = app('config')['app']['charset'];
		$of = strtolower($request->input('of'));
		$jsonp = $request->input('jsonp'); 
		empty($jsonp) && $jsonp = $request->input('callback');
		$types = ['xml' => 'text/xml', 'yaml' => 'application/yaml', 'json' => 'application/json', 'jsonp' => 'application/x-javascript', 'script' => 'application/x-javascript', 'csv' => 'application/vnd.ms-excel', 'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text' => 'text/plain', 'html' => 'text/html'];
		if (!array_key_exists($of, $types))
		{
			if ($request->ajax())
				$of = empty($jsonp) ? 'json' : 'jsonp';
			else
				$of = 'html';
		}
		$content = $of != 'html' ? Output::$of($data, $jsonp) : $this->view('tips', ['_data' => $data]);
		return response($content)->header('Content-Type', $types[$of].'; charset='.$charset);
	}
}