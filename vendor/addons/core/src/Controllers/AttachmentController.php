<?php
namespace Addons\Core\Controllers;

use Addons\Core\Models\Attachment;
use Addons\Core\Models\AttachmentFile;
use Addons\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Addons\Core\File\Mimes;
use Lang,Crypt,Agent,Image;
class AttachmentController extends Controller {

	private $model;
	public function __construct()
	{
		//解决flash上传的cookie问题
		if (isset($_POST['PHPSESSIONID']))
		{
			$session_id = Crypt::decrypt(trim($_POST['PHPSESSIONID']));
			if (!empty($session_id)) session_id($session_id);
		}
		
		parent::__construct();

		//$id = $this->request->param('do');
		//empty($_GET['id']) && !empty($id) && $this->request->query('id', $_GET['id'] = $id);
		
		$this->model = new Attachment();
	}

	public function download($id)
	{
		$id = intval($id);

		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);

		if (empty($data))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);
	
		//获取远程文件
		$this->model->sync($id);

		$full_path = $this->model->get_real_rpath($data['path']);
		$mime_type = Mimes::getInstance()->mime_by_ext($data['ext']);
		$content_length = $data['size'];
		$last_modified = $data['created_at'];
		$etag = $data['hash'];
		$cache = TRUE;
		return response()->download($full_path, $data['displayname'], [], compact('mime_type', 'etag', 'last_modified', 'content_length', 'cache'));

	}

	public function info($id)
	{
		$id = intval($id);

		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);
		unset($data['path'], $data['afid'], $data['id'], $data['basename']); //array_delete_selector($data, 'path,afid,id,basename');
		if (empty($data))
		{
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);
		}
		return $this->success('',TRUE,$data);
	}

	public function index($id, $width = NULL, $height = NULL)
	{
		$id = intval($id);
		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);

		if (empty($data))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);
		

		if (in_array(strtolower($data['ext']), array('jpg','jpeg','gif','png','bmp')))
		{
			if (!empty($width) || !empty($height))
				return $this->resize($id, $width, $height);
			else
			{
	 			if ( Agent::isMobile() && !Agent::isTablet() )
					return $this->phone($id);
				else
					return $this->preview($id);
			}
		}
		else
		{
			return $this->download($id);
		}
	}

	public function resize($id, $width = NULL, $height = NULL)
	{
		$id = intval($id);
		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);

		if (empty($data))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);
		

		if (!in_array(strtolower($data['ext']), array('jpg','jpeg','gif','png','bmp')))
			return $this->failure('attachment.failure_resize');


		//获取远程文件
		$this->model->sync($id);

		$full_path = $this->model->get_real_rpath($data['path']);
		$img = Image::make($full_path);
		if ((!empty($width) && $img->width() > $width) || (!empty($height) && $img->height() > $height))
		{
			$wh = aspect_ratio($img->width(), $img->height(), $width, $height);extract($wh);
			$new_path = storage_path(str_replace('.','[dot]',$this->model->get_relative_rpath($data['path'])).';'.$width.'x'.$height.'.'.$data['ext']);
			if (!file_exists($new_path))
			{
				!is_dir($path = dirname($new_path)) && mkdir($path, 0777, TRUE);
				$img->resize($width, $height, function ($constraint) {$constraint->aspectRatio();})->save($new_path);
			}
		} else
			$new_path = $full_path;
		unset($img);		
		$mime_type = Mimes::getInstance()->mime_by_ext($data['ext']);
		$content_length = NULL;//$data['size'];
		$last_modified = true;
		$etag = true;
		$cache = TRUE;
		return response()->download($new_path, NULL, [], compact('mime_type', 'etag', 'last_modified', 'content_length', 'cache'), NULL);
	}

	public function phone($id)
	{
		$id = intval($id);
		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);

		if (empty($data))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);
		

		if (in_array(strtolower($data['ext']), array('jpg','jpeg','gif','png','bmp')))
 			return $this->resize($id, 640, 960);
		else
			return $this->preview($id);
	}

	public function preview($id)
	{
		$id = intval($id);
		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$data = $this->model->get($id);

		if (empty($data))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);

		//获取远程文件
		$this->model->sync($id);

		$full_path = $this->model->get_real_rpath($data['path']);
		$mime_type = Mimes::getInstance()->mime_by_ext($data['ext']);
		$content_length = $data['size'];
		$last_modified = $data['created_at'];
		$etag = $data['hash'];
		$cache = TRUE;
		return response()->download($full_path, NULL, [], compact('mime_type', 'etag', 'last_modified', 'content_length', 'cache'), NULL);
	}

	public function redirect($id)
	{
		$id = intval($id);
		if (empty($id))
			return $this->error_param()->setStatusCode(404);

		$link_path = $this->model->get_symlink_url($id);

		if (empty($link_path))
			return $this->failure('attachment.failure_noexists')->setStatusCode(404);

		redirect($link_path);
	}

	public function uploader_query()
	{
		$result = $this->model->upload($this->user['id'], 'Filedata');
		if (!is_array($result))
			return $this->failure_attachment($result);
		return $this->success('', FALSE, $result);
	}

	public function hash_query(Request $request)
	{
		$hash = $request->input('hash');
		$size = $request->input('size');
		$filename = $request->input('filename');
		$ext = $request->input('ext');

		if (empty($hash) || empty($size) || empty($filename))
			return $this->error_param()->setStatusCode(404);
		$result = $this->model->hash($this->user['id'], $hash, $size, $filename);
		if (!is_array($result))
			return $this->failure_attachment($result);
		return $this->success('', FALSE, $result);
	}

	public function kindeditor_upload_query()
	{
		$data = array('error' => 0, 'url' => '');
		if (empty($this->user['id'])) //检查是否登录
			$data = array('error' => 1, 'message' => Kohana::message('common', 'default.failure_unlogin'));
		else
		{
			$result = $this->model->upload($this->user['id'], 'Filedata');
			if (!is_array($result))
			{
				$_config = Kohana::$config->load('attachment');
				$msg = Kohana::message('common','attachment.'.$result.'.content');
				$msg = __($msg, array(':maxsize' => Text::bytes($_config['maxsize']),':ext' => implode(',', $_config['ext'])));
				$data = array('error' => 1, 'message' => $msg);
			} else
				$data['url'] = $this->model->get_url($result['id'], TRUE, $result['original_basename']);
		}
		return $this->output($data);
	}

	public function ueditor_upload_query($action, $start = 0, $size = NULL)
	{
		$data = array();
		$_config = config('attachment');

		$page = !empty($size) ? ceil($start / $size) : 1;
		$pagesize = $size;
		switch ($action) {
			case 'config':
				$data = array(
					/* 上传图片配置项 */
					'imageActionName' => 'uploadimage', /* 执行上传图片的action名称 */
					'imageFieldName' => 'Filedata', /* 提交的图片表单名称 */
					'imageCompressEnable' => true, /* 是否压缩图片,默认是true */
					'imageCompressBorder' => 1600, /* 图片压缩最长边限制 */
					'imageUrlPrefix' => '',
					'imageInsertAlign' => 'none', /* 插入的图片浮动方式 */
					'imageAllowFiles' => array_map(function($v) {return '.'.$v;}, $_config['file_type']['image']),
					/* 涂鸦图片上传配置项 */
					'scrawlActionName' => 'uploadscrawl', /* 执行上传涂鸦的action名称 */
					'scrawlFieldName' => 'Filedata', /* 提交的图片表单名称 */
					'scrawlUrlPrefix' => '', /* 图片访问路径前缀 */
					'scrawlInsertAlign' => 'none',
					/* 截图工具上传 */
					'snapscreenActionName' => 'uploadimage', /* 执行上传截图的action名称 */
					'snapscreenUrlPrefix' => '', /* 图片访问路径前缀 */
					'snapscreenInsertAlign' => 'none', /* 插入的图片浮动方式 */
					/* 抓取远程图片配置 */
					'catcherLocalDomain' => array('127.0.0.1', 'localhost', 'img.bidu.com'),
					'catcherActionName' => 'catchimage', /* 执行抓取远程图片的action名称 */
					'catcherFieldName' => 'Filedata', /* 提交的图片列表表单名称 */
					'catcherUrlPrefix' => '', /* 图片访问路径前缀 */
					'catcherAllowFiles' => array_map(function($v) {return '.'.$v;}, $_config['file_type']['image']),
					/* 上传视频配置 */
					'videoActionName' => 'uploadvideo', /* 执行上传视频的action名称 */
					'videoFieldName' => 'Filedata', /* 提交的视频表单名称 */
					'videoUrlPrefix' => '', /* 视频访问路径前缀 */
					'videoAllowFiles' => array_map(function($v) {return '.'.$v;}, $_config['file_type']['video'] + $_config['file_type']['audio']),
					/* 上传文件配置 */
					'fileActionName' => 'uploadfile', /* controller里,执行上传视频的action名称 */
					'fileFieldName' => 'Filedata', /* 提交的文件表单名称 */
					'fileUrlPrefix' => '', /* 文件访问路径前缀 */
					'fileAllowFiles' => array_map(function($v) {return '.'.$v;}, $_config['ext']),
					/* 列出指定目录下的图片 */
					'imageManagerActionName' => 'listimage', /* 执行图片管理的action名称 */
					'imageManagerInsertAlign' => 'none', /* 插入的图片浮动方式 */
					'imageManagerUrlPrefix' => '',
					/* 列出指定目录下的文件 */
					'fileManagerActionName' => 'listfile', /* 执行文件管理的action名称 */
					'fileManagerUrlPrefix' => '',
				);
				break;
			 /* 上传图片 */
			case 'uploadimage':
			/* 上传视频 */
			case 'uploadvideo':
			/* 上传文件 */
			case 'uploadfile':
				$result = $this->model->upload($this->user['id'], 'Filedata');
				$data = !is_array($result) ? array('state' => $this->read_message($result)) : array(
					'state' => 'SUCCESS',
					'url' => $this->model->get_url($result['id'], TRUE, $result['original_basename']),
					'title' => $result['src_basename'],
					'original' => $result['src_basename'],
					'type' => !empty($result['ext']) ? '.'.$result['ext'] : '',
					'size' => $result['size'],
				);
				break;
			/* 上传涂鸦 */
			case 'uploadscrawl':
				$file_path = tempnam(sys_get_temp_dir(),'');
				$fp = fopen($file_path,'wb+');
				fwrite($fp, base64_decode($_POST['Filedata']));
				fclose($fp);
				$result = $this->model->save($this->user['id'], $file_path, 'scrawl_'.$this->user['id'].'_'.date('Ymdhis').'.png');
				$data = !is_array($result) ? array('state' => $this->read_message($result)) : array(
					'state' => 'SUCCESS',
					'url' => $this->model->get_url($result['id'], TRUE, $result['original_basename']),
					'title' => $result['src_basename'],
					'original' => $result['src_basename'],
					'type' => !empty($result['ext']) ? '.'.$result['ext'] : '',
					'size' => $result['size'],
				);
				break;
			/* 抓取远程文件 */
			case 'catchimage':
				$url = isset($_POST['Filedata']) ? $_POST['Filedata'] : $_GET['Filedata'];
				$url = to_array($url);$list = array();
				foreach ($url as $value) {
					$result = $this->model->download($this->user['id'], $value);
					$list[] = !is_array($result) ? array('state' => $this->read_message($result), 'source' => $value) : array (
						'state' => 'SUCCESS',
						'url' => $this->model->get_url($result['id'], TRUE, $result['original_basename']),
						'title' => $result['src_basename'],
						'original' => $result['src_basename'],
						'size' => $result['size'],
						'source' => $value,
					);
				}
				$data = array(
					'state'=> !empty($list) ? 'SUCCESS' : 'ERROR',
					'list'=> $list,
				);
				break;
			 /* 列出图片 */
			case 'listimage':
			/* 列出文件 */
			case 'listfile':
				$list = $this->model->search(array('ext' => $_config['file_type']['image'], 'duplicate' => TRUE), array('a.created_at' => 'DESC'), $page, $pagesize);
				
				$data = array(
					'state' => 'SUCCESS',
					'list' => array_values(array_map(function($v) {return array('url' => $this->model->get_url($v['id'], TRUE, $v['original_basename']));}, $list['data'])),
					'start' => $list['page'] * $list['pagesize'],
					'total' => $list['count'],
				);
				break;
			default:
				break;
		}
		return $this->output($data);
	}

	public function upload_avatar_query()
	{
		$this->checkauth(); //检查是否登录

		$input = file_get_contents('php://input');
		$data = explode('--------------------', $input);
		//@file_put_contents('./avatar_1.jpg', $data[0]);
		$file_path = tempnam(sys_get_temp_dir(),'');
		$fp = fopen($file_path,'wb+');
		fwrite($fp, $data[0]);
		fclose($fp);

		$attachment = $this->model->save($this->user['id'], $file_path, 'avatar_'.$this->user['id'].'_'.date('Ymdhis').'.jpg');
		$url = $this->model->get_url($attachment['id'], TRUE, $attachment['original_basename']);
		return $this->success('', $url, array('id' => $attachment['id'], 'url' => $url));
	}

	public function upload_dataurl_query(Request $request)
	{
		$dataurl = $request->post('DataURL');
		
		$part = parse_dataurl($dataurl);
		$ext = Mimes::getInstance()->ext_by_mime($part['mine']);
		$data = $part['data'];
		$file_path = tempnam(sys_get_temp_dir(),'');
		$fp = fopen($file_path,'wb+');
		fwrite($fp, $data);
		fclose($fp);
		unset($dataurl, $data, $part);

		$attachment = $this->model->save($this->user['id'], $file_path, 'datauri_'.$this->user['id'].'_'.date('Ymdhis').'.'.$ext);
		$url = $this->model->get_url($attachment['id'], TRUE, $attachment['original_basename']);
		return $this->success('', $url, array('id' => $attachment['id'], 'url' => $url));
	}


	private function read_message($message_field)
	{
		$_config = config('attachment');
		$_data =  ['maxsize' => $_config['maxsize'], 'ext' => implode(',', $_config['ext'])];
		return Lang::has($message = 'attachment.'.$message_field.'.content') ? trans($message, $_data) : trans('core::common.'.$message, $_data);
	}
}