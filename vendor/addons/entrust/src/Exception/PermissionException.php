<?php
namespace Addons\Entrust\Exception;

use Addons\Entrust\Permission;
use RuntimeException;

class PermissionException extends RuntimeException
{
	private $permission = null;

	public function __construct(Permission $permission = null)
	{
		$this->message = 'You have no permission to access this page.';
		!empty($permission) && $this->setPermission($permission);
	}

	public function setPermission(Permission $permission)
	{
		$this->permission = $permission;
		$this->message = 'You have no permission to access this page. You nend permission named: '.$permission->display_name;
	}

	public function getPermission()
	{
		return $this->permission;
	}


}