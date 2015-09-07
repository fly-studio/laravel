<?php
namespace Addons\Core\Controllers;

use Addons\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Addons\Core\Models\Manual;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Addons\Core\Validation\ValidatesRequests;

use Cache;
class ToolsController extends Controller {

	public function index()
	{
		return $this->view('system.tools');
	}

	public function clear_cache_query()
	{
		Cache::flush();
		return $this->success(array('title' => '操作成功', 'content' => '缓存清理成功'), FALSE);
	}

	public function create_static_folder_query()
	{
		$target_path = normalize_path(APPPATH.'../static');
		$link_path = normalize_path(APPPATH . 'static/common');
		
		@unlink($link_path);@rmdir($link_path);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && version_compare(php_uname('r'), '6.0', '<')) { //Windows Vista以下
			exec('"'.$target_path.'/bin/junction.exe" -d "'.$link_path.'"');
			exec('"'.$target_path.'/bin/junction.exe" "'.$link_path.'" "'.$target_path.'"');
		} else {
			@symlink($target_path, $link_path);
		}

		return $this->success(array('title' => '指向成功', 'content' => 'static目录指向成功'), FALSE);
	}
}