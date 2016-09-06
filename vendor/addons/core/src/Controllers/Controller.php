<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Role;
//Facades
use Addons\Core\Controllers\OutputTrait;
use Addons\Core\Controllers\InitTrait;
use Addons\Core\Controllers\PermissionTrait;
class Controller extends BaseController {
	use InitTrait, PermissionTrait, OutputTrait;
	
	public function callAction($method, $parameters)
	{
		$this->initCommon();
		$this->initMember();
		foreach(['site', 'fields', 'user'] as $key)
			$this->viewData['_'.$key] = &$this->$key;
		if( !$this->checkPermission($this->user, $method) )
		{
			in_array(app('request')->input('of'), ['csv', 'xls', 'xlsx', 'pdf']) && app('request')->offsetSet('of', '');
			return $this->failure('auth.failure_permission');
		}

		return call_user_func_array([$this, $method], $parameters);
	}

}