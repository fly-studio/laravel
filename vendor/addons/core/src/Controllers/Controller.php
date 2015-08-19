<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Addons\Core\Models\Role;
use Addons\Core\Models\Field;
use Addons\Core\Output;
use Addons\Core\File\Mimes;
//Trait
use Addons\Core\Controllers\OutputTrait;
//Facades
use Auth, Lang;

class Controller extends BaseController {
	use OutputTrait;

	public $site;
	public $fields;
	public $user;
	public $roles;

	public function __construct()
	{
		$this->beforeFilter('csrf', ['on' => 'post']);
		/*Init*/
		$this->initCommon();
		$this->initMember();
	}

	private function initCommon()
	{
		$this->site = app('config')->get('site');
		$this->fields = (new Field)->getFields();
		$this->site['titles'][] = ['title' => $this->site['title'], 'url' => '', 'target' => '_self'];
	}

	private function initMember()
	{
		$this->user = Auth::viaRemember() || Auth::check() ? Auth::User()->toArray() : ['id' => 0, 'rid' => 0];
		$this->roles = (new Role)->getRoles();
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
		$this->site['title_reverse'] && $this->site['titles'] = array_reverse($this->site['titles']);
		
		return view($filename, $data)->with('_site', $this->site)->with('_user', $_user)->with('_roles', $this->roles)->with('_fields', $this->fields);
	}

	protected function response($content, $status = 200, array $headers = [])
	{
		$response = response($content, $status, $headers)->header('P3P','CP="CAO PSA OUR"');
		if (in_array(app('request')->method(), array( 'POST', 'PUT', 'DELETE' )))
		{
			//header no cache when post
			foreach([
				'Expires' => '0',
				'Cache-Control' => 'no-store,private, post-check=0, pre-check=0, max-age=0',
				'Pragma' => 'no-cache',
			] as $k => $v)
				$response->header($k, $v);
		}
		return $response;
	}


	protected function error($message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->_make_output('error', $message_name, $url, $data, $export_data);
	}

	protected function success($message_name = NULL, $url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->_make_output('success', $message_name, $url, $data, $export_data);
	}

	protected function failure($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		return $this->_make_output('failure', $message_name, $url, $data, $export_data);
	}

	protected function error_param($url = FALSE)
	{
		return $this->error('default.error_param',$url);
	}

	protected function success_login($url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->success(Lang::has('auth.success') ? 'auth.success' : 'default.success_login', $url, $data, $export_data);
	}

	protected function failure_login($url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->failure(Lang::has('auth.failure') ? 'auth.failure' : 'default.failure_login', $url, $data, $export_data);
	}

	protected function failure_validate(\Illuminate\Support\MessageBag $messagebag)
	{
		$errors = $messagebag->toArray();
		$messages = [];
		foreach ($errors as $lines) {
			foreach ($lines as $message) {
				$messages[] = trans('default.failure_validate.list', compact('message'));
			}
		}
		return $this->_make_output('failure', 'default.failure_validate', FALSE, ['errors' => $errors, 'messages' => implode($messages)], TRUE);
	}

	protected function failure_attachment($error_no, $url = FALSE)
	{
		$_config = config('attachment');
		return $this->failure('attachment.'.$error_no, $url, ['maxsize' => format_bytes($_config['maxsize']), 'ext' => implode(',', $_config['ext'])]);
	}

	protected function _make_output($type, $message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		$msg = $message_name;
		if (!is_array($message_name))
		{
			$msg = Lang::has($message_name) ? trans($message_name) : (Lang::has('core::common.'.$message_name) ? trans('core::common.'.$message_name) : []);
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
			'uid' => $this->user['id'],
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
		
		if (!in_array($of, ['js', 'json', 'jsonp', 'xml', 'txt', 'text', 'csv', 'xls', 'xlsx', 'yaml', 'html', 'pdf' ]))
		{
			if ($request->ajax()) //自动切换ajax提交为json数据
				$of = empty($jsonp) ? 'json' : 'jsonp';
			else
				$of = 'html';
		}
		if (in_array($of, ['yaml', 'csv', 'xls', 'xlsx', 'pdf']))
		{
			$filename = Output::$of($data['data']);
			return response()->download($filename, date('YmdHis').'.'.$of, ['Content-Type' =>  Mimes::getInstance()->mime_by_ext($of)])->deleteFileAfterSend(TRUE);
		} else {
			$content = $of != 'html' ? Output::$of($data, $jsonp) : $this->view('tips', ['_data' => $data]);
			return $this->response($content)->header('Content-Type', Mimes::getInstance()->mime_by_ext($of).'; charset='.$charset);
		}
	}
}