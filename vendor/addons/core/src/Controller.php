<?php
namespace Addons\Core;

use Illuminate\Routing\Controller as BaseController;
use Addons\Core\Model\Role;
//Trait
use Addons\Core\Controller\OutputTrait;
//Facades
use Auth;
class Controller extends BaseController {
	use OutputTrait;

	public $site;
	public $fields;
	public $user;
	public $role;
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
		$this->fields = [];
		$this->site['titles'][] = ['title' => $this->site['title'], 'url' => '', 'target' => '_self'];
	}

	private function initMember()
	{
		$this->user = Auth::viaRemember() || Auth::check() ? Auth::User()->toArray() : ['uid' => 0, 'rid' => 0];
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
		
		return view($filename, $data)->with('_site', $this->site)->with('_user', $_user)->with('_role', $this->role)->with('_fields', $this->fields);
	}
}