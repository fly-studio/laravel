<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Addons\Core\Models\Role;
use Addons\Core\Models\Field;
use Addons\Core\Output;
use Addons\Core\File\Mimes;

//Facades
use Auth, Lang;

class Controller extends BaseController {

	/**
	 * RBAC权限表，注意：只有被路由调用的函数才会检查权限
	 * @example  ['index,show' => 'member.view', 'edit,update,create,store' => 'member.edit', 'destory' => 'member.delete']
	 * @example  ['*' => 'member.view', 'edit,update,create,store' => 'member.edit', 'destory' => 'member.delete'] 此配置同上，* 代表所有未配置的函数名
	 * @example  ['*' => ['member.view', 'dashborad.view']] * 代表所有未配置的函数名，此例也就是代表所有函数，其中权限可为数组
	 *  
	 * @var array
	 */
	public $permissions = [];
	/**
	 * 设置本名称后，将自动为本名称加上一个通用的权限
	 * 查看 initPermissions
	 * 
	 * @var string
	 */
	public $RESTful_permission = NULL;

	public $site;
	public $fields;
	public $user;
	//public $roles;

	private $viewData = [];

	public function __construct()
	{
		//$this->beforeFilter('csrf', ['on' => 'post']);
		/*Init*/
		$this->initCommon();
		$this->initMember();
		$this->initPermissions();

	}

	private function initPermissions()
	{
		$_permissions = [];
		foreach($this->permissions as $k => $v)
		{
			foreach(explode(',', $k) as $key)
				$_permissions[strtolower($key)] = $v;
		}
		$rest = $this->RESTful_permission;
		if (!empty($rest))
		{
			$_permissions += [
				'index' => $rest.'.view',
				'show' => $rest.'.view',
				'export' => $rest.'.export',
				'edit' => $rest.'.edit',
				'update' => $rest.'.edit',
				'create' => $rest.'.create',
				'store' => $rest.'.create',
				'destory' => $rest.'.destory',
			];
		}
		$this->permissions = $_permissions;
	}

	private function initCommon()
	{
		$this->viewData['_site'] = app('config')->get('site');
		$this->viewData['_fields'] = (new Field)->getFields();

		$this->site = & $this->viewData['_site'];
		$this->fields = & $this->viewData['_fields'];
		$this->site['titles'][] = ['title' => $this->site['title'], 'url' => '', 'target' => '_self'];
	}

	private function initMember()
	{
		$this->viewData['_user'] = Auth::check() ? Auth::User() : new \App\User;
		//$this->viewData['_roles'] = (new Role)->getRoles();

		$this->user = & $this->viewData['_user'];
		//$this->roles = & $this->viewData['_roles'];
	}

	protected function subtitle($title, $url = NULL, $target = '_self')
	{
		$this->site['titles'][] = compact('title', 'url', 'target');
	}

	public function __set($key, $value)
	{
		$this->viewData[$key] = $value;
		view()->share($key, $value);
	}

	public function __get($key)
	{
		return $this->viewData[$key];
	}

	public function __isset($key)
	{
		return isset($this->viewData[$key]);
	}

	public function __unset($key)
	{
		unset($this->viewData[$key]);
	}

	protected function view($filename, $data = [])
	{
		$this->site['title_reverse'] && $this->site['titles'] = array_reverse($this->site['titles']);
		
		return view($filename, $data)->with($this->viewData);
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

	public function error($message_name = NULL, $url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->_make_output('error', $message_name, $url, $data, $export_data);
	}

	public function success($message_name = NULL, $url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->_make_output('success', $message_name, $url, $data, $export_data);
	}

	public function failure($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		return $this->_make_output('failure', $message_name, $url, $data, $export_data);
	}

	public function warning($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		return $this->_make_output('warning', $message_name, $url, $data, $export_data);
	}

	public function notice($message_name = NULL, $url = FALSE, array $data = [],$export_data = FALSE)
	{
		return $this->_make_output('notice', $message_name, $url, $data, $export_data);
	}

	protected function error_param($url = FALSE)
	{
		return $this->error('server.error_param',$url);
	}

	protected function success_login($url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->success('auth.success_login', $url, $data, $export_data);
	}

	protected function success_logout($url = TRUE, array $data = [], $export_data = TRUE)
	{
		return $this->success('auth.success_logout', $url, $data, $export_data);
	}

	protected function failure_login($url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->failure('auth.failure_login', $url, $data, $export_data);
	}

	protected function failure_validate(\Illuminate\Support\MessageBag $messagebag)
	{
		$errors = $messagebag->toArray();
		$messages = [];
		foreach ($errors as $lines) {
			foreach ($lines as $message) {
				$messages[] = trans(Lang::has('validation.failure_post.list') ? 'validation.failure_post.list' : 'core::common.validation.failure_post.list', compact('message'));
			}
		}
		return $this->_make_output('failure', 'validation.failure_post', FALSE, ['errors' => $errors, 'messages' => implode($messages)], TRUE);
	}

	protected function failure_attachment($error_no, $url = FALSE)
	{
		$_config = config('attachment');
		return $this->failure('attachment.'.$error_no, $url, ['maxsize' => format_bytes($_config['maxsize']), 'ext' => implode(',', $_config['ext'])]);
	}

	protected function failure_noexists($url = FALSE, array $data = [], $export_data = FALSE)
	{
		return $this->failure('document.failure_noexist', $url, $data, $export_data);
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
			if ($request->ajax()) //自动切换ajax状态下of为json
				$of = empty($jsonp) ? 'json' : 'jsonp';
			else
				$of = 'html';
		}
		if (in_array($of, ['csv', 'xls', 'xlsx', 'pdf']))
		{
			$filename = Output::$of($data['data']);
			return response()->download($filename, date('YmdHis').'.'.$of, ['Content-Type' =>  Mimes::getInstance()->mime_by_ext($of)])->deleteFileAfterSend(TRUE);
		} else {
			$content = $of != 'html' ? Output::$of($data, $jsonp) : $this->view('tips', ['_data' => $data]);
			return $this->response($content)->header('Content-Type', Mimes::getInstance()->mime_by_ext($of).'; charset='.$charset);
		}
	}

	private function checkPermission($method)
	{
		$method = strtolower($method);
		!isset($this->permissions[$method]) && $method = '*';
		return array_key_exists($method, $this->permissions) ? $this->user->can($this->permissions[$method], true) : true;
	}

	public function callAction($method, $parameters)
	{
		if( !$this->checkPermission($method) )
		{
			in_array(app('request')->input('of'), ['csv', 'xls', 'xlsx', 'pdf']) && app('request')->offsetSet('of', '');
			return $this->failure('auth.failure_permission');
		}
		return call_user_func_array([$this, $method], $parameters);
	}
}