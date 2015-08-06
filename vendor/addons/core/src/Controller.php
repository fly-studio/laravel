<?php
namespace Addons\Core;


use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Addons\Core\Output;
use Addons\Smarty\View\Engine;

use Illuminate\Support\Facades\Lang;
use Addons\Core\Facades\Core;

class Controller extends BaseController {

	public $site;
	public $user;
	public $role;
	public $fields;

	public function __construct()
	{
		
		$this->beforeFilter('csrf', ['on' => 'post']);
		/*Init*/
		$this->site = app('config')->get('site');
		$this->user = [];
		$this->role = [];
		$this->fields = [];

		$this->site['titles'][] = ['title' => $this->site['title'], 'url' => '', 'target' => '_self'];
	}

	protected function subtitle($title, $url = NULL, $target = '_self')
	{
		$this->site['titles'][] = compact('title', 'url', 'target');
	}

	public function __set($key, $value)
	{
		view()->share($key, $value);
	}

	protected function view($filename, $data = [])
	{
		$_user = array_delete_selector($this->user, 'password');
		$this->site['titles'] = !$this->site['title_reverse'] ? $this->site['titles'] : array_reverse($this->site['titles']);
		
		return view($filename, $data)->with('_site', $this->site)->with('_user', $_user)->with('_role', $this->role)->with('_fields', $this->fields);
	}

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
			$msg = Lang::has($message_name)? trans($message_name) : [];
			$default = trans('core::common.default.'.$type );
			$msg = _extends( $msg, $default); //填充

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

	protected function output(array $data)
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