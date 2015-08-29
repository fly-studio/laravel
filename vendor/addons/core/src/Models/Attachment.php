<?php

namespace Addons\Core\Models;

use Addons\Core\Models\Model;

use \Curl\Curl;
use Addons\Core\SSH;
use Addons\Core\Models\CacheTrait;
class Attachment extends Model{
	
	protected $guarded = ['id'];

	const UPLOAD_ERR_MAXSIZE = 100;
	const UPLOAD_ERR_EMPTY = 101;
	const UPLOAD_ERR_EXT = 102;
	const UPLOAD_ERR_SAVE = 106;
	const DOWNLOAD_ERR_URL = 104;
	const DOWNLOAD_ERR_FILE = 105;

	private $fileModel,$_config;

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		$this->fileModel = new AttachmentFile();
		$this->_config = config('attachment');
	}

	public function file()
	{
		return $this->hasOne('Addons\\Core\\Models\\AttachmentFile', 'id', 'afid');
	}


	public function upload($uid, $field_name, $description = '')
	{
		if (!isset($_FILES[$field_name]) || !is_uploaded_file($_FILES[$field_name]["tmp_name"]) || $_FILES[$field_name]["error"] != 0) {
			return $_FILES[$field_name]['error'];
		}

		//ignore_user_abort(TRUE);
		set_time_limit(0);

		//$ext = strtolower(pathinfo($_FILES[$field_name]['name'],PATHINFO_EXTENSION));

		return $this->savefile($uid, $_FILES[$field_name]["tmp_name"], $_FILES[$field_name]['name'], NULL, NULL, $description);
	}

	public function download($uid, $url, $filename = NULL, $ext = NULL)
	{
		if (empty($url))
			return self::DOWNLOAD_ERR_URL;

		ignore_user_abort(TRUE);
		set_time_limit(0);

		$file_path = tempnam('','');
		$fp = fopen($file_path,'wb+');

		$curl = new Curl();
		$curl->setOpt(CURLOPT_FILE, $fp);
		$curl->setOpt(CURLOPT_BINARYTRANSFER, TRUE);
		$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
		$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
		$curl->get($url);
		fclose($fp);

		if ($curl->error)
			return self::DOWNLOAD_ERR_FILE;

		$download_filename = $curl->responseHeaders['Content-Disposition'];

		$basename = mb_basename($url);//pathinfo($url,PATHINFO_BASENAME);
		if (!empty($download_filename))
		{
			preg_match('/filename\s*=\s*(\S*)/i',  $download_filename, $tmp);
			!empty($tmp) && $basename = mb_basename(trim($tmp[1],'\'"'));//pathinfo($download_filename,PATHINFO_BASENAME);
		}

		return $this->savefile($uid, $file_path, $basename, $filename, $ext, 'Download from:' . $url);

	}

	public function hash($uid, $hash, $size, $original_basename, $file_name = NULL, $file_ext = NULL, $description = NULL)
	{
		if (empty($hash) || empty($size))
			return FALSE;
		$file = $this->fileModel->get_byhash($hash, $size);
		if (empty($file))
			return FALSE;

		is_null($file_ext) && $file_ext = strtolower(pathinfo($original_basename, PATHINFO_EXTENSION));
		is_null($file_name) && $file_name = mb_basename($original_basename, '.'.$file_ext); //支持中文的basename
		
		$attachment = $this->create([
			'afid' => $file->id,
			'filename' => $file_name,
			'ext' => $file_ext,
			'original_basename' => $original_basename,
			'description' => $description,
			'uid' => $uid,
		]);
		return $this->get($attachment->id);
	}

	public function savefile($uid, $original_file_path, $original_basename, $file_name = NULL, $file_ext = NULL, $description = NULL)
	{
		if (!file_exists($original_file_path))
			return FALSE;

		is_null($file_ext) && $file_ext = strtolower(pathinfo($original_basename, PATHINFO_EXTENSION));
		is_null($file_name) && $file_name = mb_basename($original_basename, '.'.$file_ext); //支持中文的basename

		$size = filesize($original_file_path);

		if(!in_array($file_ext, $this->_config['ext']))
			return self::UPLOAD_ERR_EXT;
		if ($size > $this->_config['maxsize'])
			return self::UPLOAD_ERR_MAXSIZE;
		if (empty($size))
			return self::UPLOAD_ERR_EMPTY;

		//传文件都耗费了那么多时间,还怕md5?
		$hash = md5_file($original_file_path);

		$file = $this->fileModel->get_byhash($hash, $size);
		if (empty($file))
		{
			$new_basename = $this->_get_hash_basename();
			$new_hash_path = $this->get_hash_path($new_basename);

			if (!$this->_save_file($original_file_path, $new_basename))
				return self::UPLOAD_ERR_SAVE;

			$file = AttachmentFile::create([
				'basename' => $new_basename,
				'path' => $new_hash_path,
				'hash' => $hash,
				'size' => $size,
			]);
		}
		else //已经存在此文件
			@unlink($original_file_path);

		$attachment = $this->create([
			'afid' => $file->id,
			'filename' => $file_name,
			'ext' => $file_ext,
			'original_basename' => $original_basename,
			'description' => $description,
			'uid' => $uid,
		]);
		return $this->get($attachment->id);
	}



	public function get($id)
	{
		$attachment = self::find($id);
		$result = [];
		if (!empty($attachment))
		{
			$result = $attachment->toArray() + $attachment->file->toArray();
			$result['displayname'] = $result['filename'].(!empty($result['ext']) ?  '.'.$result['ext'] : '' );
		}
		return $result;
	}

	public function get_type($ext)
	{
		$ext = strtolower($ext);
		foreach ($this->_config['file_type'] as $key => $value)
		{
			if (in_array($ext, $value))
				return $key;
		}
		return NULL;
	}

	/**
	 * 获取软连接的网址
	 * 
	 * @param  integer $id     AID
	 * @param  boolean $protocol 是否有域名部分
	 * @return string
	 */
	public function get_symlink_url($id, $protocol = NULL)
	{
		$path = $this->_create_symlink($id);
		if (empty($path))
			return FALSE;

		return url(str_replace(APPPATH, '', $path));
	}

	/**
	 * 根据数据库中的路径得到绝对路径
	 * 
	 * @param  string $hash_path 数据库中取出的路径
	 * @return string                绝对路径
	 */
	public function get_real_rpath($hash_path)
	{
		return APPPATH.$this->get_relative_rpath($hash_path);
	}

	/**
	 * 根据数据库中的路径得到远程绝对路径
	 * 
	 * @param  string $hash_path 数据库中取出的路径
	 * @return string                远程绝对路径
	 */
	public function get_remote_rpath($hash_path)
	{
		return $this->_config['remote']['path'].$hash_path;
	}

	/**
	 * 根据附件文件名，获取文件的绝对地址
	 * 
	 * @param  string $basename 附件文件名
	 * @return string
	 */
	public function get_real_path($basename)
	{
		return APPPATH.$this->get_relative_path($basename);
	}

	/**
	 * 根据附件文件名获得文件的相对路径
	 * 	
	 * @param  string $basename 附件文件名
	 * @return string           相对路径
	 */
	public function get_relative_path($basename)
	{
		return $this->_config['local']['path'].$this->get_hash_path($basename);
	}

	/**
	 * 根据数据库中的路径获得文件的相对路径
	 * 	
	 * @param  string $hash_path 数据库中的路径
	 * @return string            相对路径
	 */
	public function get_relative_rpath($hash_path)
	{
		return $this->_config['local']['path'].$hash_path;
	}

	/**
	 * 构造一个符合router标准的URL
	 * 
	 * @param  integer $id      AID
	 * @param  boolean $protocol 是否有域名部分
	 * @param  string $filename  需要放在网址结尾的文件名,用以欺骗浏览器
	 * @return string
	 */
	public function get_url($id, $filename = NULL)
	{
		return  url(!empty($filename) ? 'attachment/'.$id.'/'.urlencode($filename) : 'attachment?id='.$id);
	}

	/**
	 * 根据附件名称获得相对路径
	 * 	
	 * @param  string $basename 附件文件名
	 * @return string           相对路径
	 */
	protected function get_hash_path($basename)
	{
		$md5 = md5($basename . md5($basename));
		return $md5[0].$md5[1].'/'.$md5[2].$md5[3].'/'.$md5[4].$md5[5].','.$basename;
	}

	/**
	 * 获取一个不存在的文件名称
	 * 
	 * @return [type] [description]
	 */
	protected function _get_hash_basename()
	{
		do
		{
			$basename = uniqid(date('YmdHis,') . rand(100000,999999) . ',')  . (!empty($this->_config['normal_ext']) ? '.' . $this->_config['normal_ext'] : '');
			$file = $this->fileModel->get_bybasename($basename);
		} while (!empty($file));
		return $basename;
	}

	/**
	 * 在cache目录下创建一个软连接
	 * 
	 * @param  integer $id AID
	 * @return string
	 */
	protected function _create_symlink($id, $life_time = Date::DAY)
	{
		$data = $this->get($id);
		if (empty($data))
			return FALSE;
		//将云端数据同步到本地
		$this->remote && $this->sync($id);
		$path = storage_path($this->_config['local']['path'].'attachment,'.md5($id).'.'.$data['ext']);
		!file_exists($path) && @symlink($this->get_real_rpath($data['path']), $path);
		return $path;
	}

	/**
	 * 删除软连接
	 * 
	 * @param  integer $id AID
	 * @return
	 */
	public function unlink_symlink($id)
	{
		$data = $this->get($id);
		if (empty($data))
			return FALSE;
		$path = storage_path($this->_config['local']['path'].'attachment,'.md5($id).'.'.$data['ext']);
		@unlink($path);
	}

	protected function _save_file($original_file_path, $new_basename)
	{
		$result = FALSE;
		$new_hash_path = $this->get_hash_path($new_basename);
		
		if ($this->_config['remote']['enabled']) //远程存储打开
		{
			$ssh = new SSH((array)$this->_config['remote']);

			$newpath = $this->get_remote_rpath($new_hash_path);
			$dir = dirname($newpath);

			!$ssh->is_dir($dir) && @$ssh->mkdir($dir, $this->_config['remote']['folder_mod'], TRUE);
			!empty($this->_config['remote']['folder_own']) && @$ssh->chown($dir, $this->_config['remote']['folder_own']);
			!empty($this->_config['remote']['folder_grp']) && @$ssh->chgrp($dir, $this->_config['remote']['folder_grp']);

			if (!($result = @$ssh->send_file($original_file_path, $newpath)))
			{
				@unlink($original_file_path);
				return FALSE;
			}
			@$ssh->chmod($newpath, $this->_config['remote']['file_mod']);
			!empty($this->_config['remote']['file_own']) && @$ssh->chown($newpath, $this->_config['remote']['file_own']);
			!empty($this->_config['remote']['file_grp']) && @$ssh->chgrp($newpath, $this->_config['remote']['file_grp']);
		}

		if ($this->_config['local']['enabled']) //本地存储打开
		{
			$newpath = $this->get_real_rpath($new_hash_path);
			$dir = dirname($newpath);

			!is_dir($dir) && @mkdir($dir, $this->_config['local']['folder_mod'], TRUE);
			!empty($this->_config['local']['folder_own']) && @chown($dir, $this->_config['local']['folder_own']);
			!empty($this->_config['local']['folder_grp']) && @chgrp($dir, $this->_config['local']['folder_grp']);
			if(is_uploaded_file($original_file_path))
				$result = @move_uploaded_file($original_file_path, $newpath);
			else
			{
				if (!($result = @rename($original_file_path, $newpath)))
				{
					$result = @copy($original_file_path, $newpath);
				}
			}
			if ($result)
			{
				@chmod($newpath, $this->_config['local']['file_mod']);
				!empty($this->_config['local']['file_own']) && @chown($newpath, $this->_config['local']['file_own']);
				!empty($this->_config['local']['file_grp']) && @chgrp($newpath, $this->_config['local']['file_grp']);
			}
		}
		@unlink($original_file_path);
		return $result;
	}

	public function sync($id, $life_time = NULL)
	{
		if ($this->_config['remote']['enabled'])
		{
			$data = $this->get($id);
			if (empty($data))
				return FALSE;

			$local = $this->get_real_rpath($data['path']);
			$remote = $this->get_remote_rpath($data['path']);

			//如果本地存在，就放弃下载
			if (file_exists($local)) return TRUE;

			$dir = dirname($local);
			!is_dir($dir) && @mkdir($dir, $this->_config['local']['folder_mod'], TRUE);
			!empty($this->_config['local']['folder_own']) && @chown($dir, $this->_config['local']['folder_own']);
			!empty($this->_config['local']['folder_grp']) && @chgrp($dir, $this->_config['local']['folder_grp']);

			$ssh = new SSH((array)$this->_config['remote']);
			$ssh->receive_file($remote, $local);
			!empty($this->_config['local']['file_own']) && @chown($newpath, $this->_config['local']['file_own']);
			!empty($this->_config['local']['file_grp']) && @chgrp($newpath, $this->_config['local']['file_grp']);

			//过期文件 删除
			is_null($life_time) && !$this->_config['local']['enabled'] && $life_time = $this->_config['local']['life_time'];
			if ($life_time > 0)
			{
				//在生命到期以后，删除本文件
			}
		}
		return TRUE;
	}
}