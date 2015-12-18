<?php

namespace Addons\Core\Models;

use Addons\Core\Models\Model;

use \Curl\Curl;
use Addons\Core\SSH;
class Attachment extends Model{
	
	protected $guarded = ['id'];
	protected $hidden = ['path', 'afid', 'basename'];


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

	public function file_type()
	{
		static $file_type;
		if (!empty($file_type)) return $file_type;

		$ext = $this->ext;
		foreach ($this->_config['file_type'] as $key => $value)
			if (in_array($ext, $value)) return $file_type = $key;

		return NULL;
	}

	public function file()
	{
		return $this->hasOne(get_namespace($this).'\\AttachmentFile', 'id', 'afid');
	}

	public function full_path()
	{
		return $this->get_real_path();
	}

	public function real_path()
	{
		return $this->get_real_path();
	}

	public function relative_path()
	{
		return $this->get_relative_path();
	}

		/**
	 * 构造一个符合router标准的URL
	 * 
	 * @param  integer $id      AID
	 * @param  boolean $protocol 是否有域名部分
	 * @param  string $filename  需要放在网址结尾的文件名,用以欺骗浏览器
	 * @return string
	 */
	public function url($filename = NULL)
	{
		empty($filename) && $filename = $this->original_basename;
		return  url('attachment/'.$this->getKey().'/'.urlencode($filename));
	}

	/**
	 * 获取软连接的网址
	 * 
	 * @return string
	 */
	public function symlink_url()
	{
		$path = $this->create_symlink(NULL);
		if (empty($path))
			return FALSE;

		return url(str_replace(APPPATH, '', $path));
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
			return static::DOWNLOAD_ERR_URL;

		ignore_user_abort(TRUE);
		set_time_limit(0);

		$file_path = tempnam(storage_path('utils'),'download-');

		$curl = new Curl();
		$curl->setOpt(CURLOPT_BINARYTRANSFER, TRUE);
		$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
		$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
		//$curl->setOpt(CURLOPT_NOSIGNAL, 1); //cURL毫秒就报错的BUG http://www.laruence.com/2014/01/21/2939.html
		$curl->download($url, $file_path);

		if ($curl->error)
			return static::DOWNLOAD_ERR_FILE;

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
			'afid' => $file->getKey(),
			'filename' => $file_name,
			'ext' => $file_ext,
			'original_basename' => $original_basename,
			'description' => $description,
			'uid' => $uid,
		]);
		return $this->get($attachment->getKey());
	}

	public function savefile($uid, $original_file_path, $original_basename, $file_name = NULL, $file_ext = NULL, $description = NULL)
	{
		if (!file_exists($original_file_path))
			return FALSE;

		is_null($file_ext) && $file_ext = strtolower(pathinfo($original_basename, PATHINFO_EXTENSION));
		is_null($file_name) && $file_name = mb_basename($original_basename, '.'.$file_ext); //支持中文的basename

		$size = filesize($original_file_path);

		if(!in_array($file_ext, $this->_config['ext']))
			return static::UPLOAD_ERR_EXT;
		if ($size > $this->_config['maxsize'])
			return static::UPLOAD_ERR_MAXSIZE;
		if (empty($size))
			return static::UPLOAD_ERR_EMPTY;

		//传文件都耗费了那么多时间,还怕md5?
		$hash = md5_file($original_file_path);

		$file = $this->fileModel->get_byhash($hash, $size);
		if (empty($file))
		{
			$new_basename = $this->_get_hash_basename();
			$new_hash_path = $this->get_hash_path($new_basename);

			if (!$this->_save_file($original_file_path, $new_basename))
				return static::UPLOAD_ERR_SAVE;

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
			'afid' => $file->getKey(),
			'filename' => $file_name,
			'ext' => $file_ext,
			'original_basename' => $original_basename,
			'description' => $description,
			'uid' => $uid,
		]);
		//当前Model更新
		//$this->setRawAttributes($attachment->getAttributes(), true);
		return $this->get($attachment->getKey());
	}



	public function get($id)
	{
		$attachment = static::find($id);
		if (!empty($attachment))
		{
			$result = $attachment->getAttributes() + $attachment->file->getAttributes();
			$result['displayname'] = $result['filename'].(!empty($result['ext']) ?  '.'.$result['ext'] : '' );
			//Model更新
			$attachment->setRawAttributes($result, true);
		}
		return $attachment;
	}


	/**
	 * 根据数据库中的路径得到绝对路径
	 * 
	 * @param  string $hash_path 数据库中取出的路径
	 * @return string                绝对路径
	 */
	private function get_real_path($hash_path = NULL)
	{
		return APPPATH.$this->get_relative_path($hash_path);
	}

	/**
	 * 根据数据库中的路径得到远程绝对路径
	 * 
	 * @param  string $hash_path 数据库中取出的路径
	 * @return string                远程绝对路径
	 */
	private function get_remote_path($hash_path = NULL)
	{
		empty($hash_path) && $hash_path = $this->file->path;
		return $this->_config['remote']['path'].$hash_path;
	}

	/**
	 * 根据数据库中的路径获得文件的相对路径
	 * 	
	 * @param  string $hash_path 数据库中的路径
	 * @return string            相对路径
	 */
	private function get_relative_path($hash_path = NULL)
	{
		empty($hash_path) && $hash_path = $this->file->path;
		return $this->_config['local']['path'].$hash_path;
	}

	/**
	 * 根据附件名称获得相对路径
	 * 	
	 * @param  string $basename 附件文件名
	 * @return string           相对路径
	 */
	protected function get_hash_path($basename = NULL)
	{
		empty($basename) && $basename = $this->file->basename;
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
	public function create_symlink($path = NULL, $life_time = 86400)
	{
		//将云端数据同步到本地
		$this->remote && $this->sync();
		$path = !empty($path) ? $path : storage_path($this->_config['local']['path'].'attachment,'.md5($this->getKey()).'.'.$this->ext);
		@unlink($path);
		symlink($this->full_path(), $path);

		//!empty($life_time) && delay_unlink($path, $life_time);
		return $path;
	}

	/**
	 * 在cache目录下创建一个硬连接
	 * 
	 * @param  integer $id AID
	 * @return string
	 */
	public function create_link($path = NULL, $life_time = 86400)
	{
		//将云端数据同步到本地
		$this->remote && $this->sync();
		$path = !empty($path) ? $path : storage_path($this->_config['local']['path'].'attachment,'.md5($this->getKey()).'.'.$this->ext);
		@unlink($path);
		link($this->full_path(), $path);

		//!empty($life_time) && delay_unlink($path, $life_time);
		return $path;
	}

	/**
	 * 在cache目录下创建一个副本
	 * 
	 * @param  integer $id AID
	 * @return string
	 */
	public function create_backup($path = NULL, $life_time = 86400)
	{
		//将云端数据同步到本地
		$this->remote && $this->sync();
		$path = !empty($path) ? $path : storage_path($this->_config['local']['path'].'attachment,'.md5($this->getKey()).'.'.$this->ext);
		@unlink($path);
		copy($this->full_path(), $path);

		//!empty($life_time) && delay_unlink($path, $life_time);
		return $path;
	}

	protected function _save_file($original_file_path, $new_basename)
	{
		$result = FALSE;
		$new_hash_path = $this->get_hash_path($new_basename);
		
		if ($this->_config['remote']['enabled']) //远程存储打开
		{
			$ssh = new SSH((array)$this->_config['remote']);

			$newpath = $this->get_remote_path($new_hash_path);
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
			$newpath = $this->get_real_path($new_hash_path);
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

	public function sync($life_time = NULL)
	{
		if ($this->_config['remote']['enabled'])
		{
			$path = $this->file->path;
			$local = $this->get_real_path($path);
			$remote = $this->get_remote_path($path);

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
			//!empty($life_time) && delay_unlink($local, $life_time);
		}
		return TRUE;
	}
}