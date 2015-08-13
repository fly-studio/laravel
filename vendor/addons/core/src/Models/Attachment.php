<?php

namespace Addons\Core\Models;

use Illuminate\Database\Eloquent\Model;

use \Curl\Curl;
class Attachment extends Model{
	const UPLOAD_ERR_MAXSIZE = 100;
	const UPLOAD_ERR_EXT = 101;
	const UPLOAD_ERR_SAVE = 106;
	const DOWNLOAD_ERR_URL = 104;
	const DOWNLOAD_ERR_FILE = 105;

	public function upload($uid, $field_name, $description = '')
	{
		if (!isset($_FILES[$field_name]) || !is_uploaded_file($_FILES[$field_name]["tmp_name"]) || $_FILES[$field_name]["error"] != 0) {
			return $_FILES[$field_name]['error'];
		}

		ignore_user_abort(TRUE);
		set_time_limit(0);

		//$ext = strtolower(pathinfo($_FILES[$field_name]['name'],PATHINFO_EXTENSION));

		return $this->save($uid, $_FILES[$field_name]["tmp_name"], $_FILES[$field_name]['name'], NULL, NULL, $description);
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

		return $this->save($uid, $file_path, $basename, $filename, $ext, 'Download from:' . $url);

	}

	public function file()
	{
		return $this->hasOne('Addons\Models\AttachmentFile.php', 'id', 'afid');
	}


	public function save($uid, $original_file_path, $original_basename, $file_name = NULL, $file_ext = NULL, $description = NULL)
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

		//传文件都耗费了那么多时间,还怕md5?
		$hash = md5_file($original_file_path);

		$file = $this->get_file_byhash($hash, $size);

		$afid = 0;

		if (empty($file))
		{
			$new_basename = $this->_get_hash_basename();
			$new_hash_path = $this->get_hash_path($new_basename);

			if (!$this->_save_file($original_file_path, $new_basename))
				return self::UPLOAD_ERR_SAVE;


			$t = DB::insert('attachment_files',array('timeline','basename','path','hash','size'))->values(array(time(), $new_basename, $new_hash_path, $hash, $size))->execute();
			$afid = array_shift($t);
		}
		else //已经存在此文件
		{
			$afid = $file['afid'];
			@unlink($original_file_path);
		}

		$query = DB::insert('attachment',array('afid','filename','ext','original_basename','description','uid','timeline'))->values(array($afid, $file_name, $file_ext, $original_basename, $description, $uid, time()));
		$t = $query->execute();
		$aid = array_shift($t);

		return $this->get($aid);
	}

	public function get_file_byhash($hash, $size)
	{
		$result = array();
		$hashkey = 'files_'.$hash.'_'.$size;
		if (is_null($result = $this->get_cache($hashkey)))
		{
			$query = DB::select('*')->from('attachment_files')->where('hash','=',$hash)->and_where('size','=',$size);
			$result = $query->execute()->current();
			$this->set_cache($hashkey, $result);
		}
		return $result;
	}

	public function get_file_bybasename($basename)
	{
		$result = array();
		$hashkey = 'files_basename_'.$basename;
		if (is_null($result = $this->get_cache($hashkey)))
		{
			$query = DB::select('*')->from('attachment_files')->where('basename','=',$basename);
			$result = $query->execute()->current();
			$this->set_cache($hashkey, $result);
		}
		return $result;
	}

	public function get_file_byhash_path($hash_path)
	{
		$result = array();
		$hashkey = 'files_hash_path_'.$basename;
		if (is_null($result = $this->get_cache($hashkey)))
		{
			$query = DB::select('*')->from('attachment_files')->where('path','=',$hash_path);
			$result = $query->execute()->current();
			$this->set_cache($hashkey, $result);
		}
		return $result;
	}

	public function get_file($afid)
	{
		$result = array();
		$hashkey = 'files_'.$afid;
		if (is_null($result = $this->get_cache($hashkey)))
		{
			$query = DB::select('*')->from('attachment_files')->where('afid','=',$afid);
			$result = $query->execute()->current();
			$this->set_cache($hashkey, $result);
		}
		return $result;
	}

	public function get($aid)
	{
		$result = array();
		$hashkey = $aid;
		if (is_null($result = $this->get_cache($hashkey)))
		{
			$query = DB::select('a.*',array('a.src_basename','original_basename'),'b.path','b.size','b.hash','b.basename')->from(array('attachment','a'))->join(array('attachment_files','b'))->on('a.afid','=','b.afid')->where('a.aid','=',$aid);
			$result = $query->execute()->current();
			
			if (!empty($result))
			{
				$result['displayname'] = $result['filename'].(!empty($result['ext']) ?  '.'.$result['ext'] : '' );
			}
			$this->set_cache($hashkey,$result);
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

	public function search($fields, $order_by = array('a.timeline' => 'DESC'), $page = 1, $pagesize = 0)
	{
		$_fields = array('aid' => array(), 'afid' => array(), 'filename' => '', 'duplicate' => NULL, 'ext' => array(), 'uid' => array(), 'timeline' => array('min' => NULL, 'max' => NULL, ), 'size' => array('min' => NULL, 'max' => NULL, ));
		$fields = to_array_selector($fields, 'aid,afid,ext,uid');
		$fields = _extends($fields, $_fields);

	
		$query = DB::select()->from(array('attachment','a'))->join(array('attachment_files','b'), 'INNER')->on('a.afid','=','b.afid');
		$fields['duplicate'] && $query->group_by('a.afid');
		!empty($fields['aid']) && $query->and_where('a.aid','IN',$fields['aid']);
		!empty($fields['afid']) && $query->and_where('a.afid','IN',$fields['afid']);
		!empty($fields['filename']) && $query->and_where('a.filename','LIKE','%'.$fields['filename'].'%');
		!empty($fields['ext']) && $query->and_where('a.ext','IN',$fields['ext']);
		!empty($fields['hash']) && $query->and_where('b.hash','=',$fields['ext']);
		!empty($fields['uid']) && $query->and_where('a.uid','IN',$fields['uid']);
		!is_null($fields['size']['min']) && $query->and_where('b.size','>=',$fields['size']['min']);
		!is_null($fields['size']['max']) && $query->and_where('b.size','<=',$fields['size']['min']);
		!is_null($fields['timeline']['min']) && $query->and_where('a.timeline','>=',$fields['timeline']['min']);
		!is_null($fields['timeline']['max']) && $query->and_where('a.timeline','<=',$fields['timeline']['max'] + 86400);
		foreach ($order_by as $key => $value)
			$query->order_by($key, $value);

		$result = $this->make_page($query, array('a.*',array('a.src_basename','original_basename'),'b.path','b.size','b.hash','b.basename'), 'a.aid', 'aid', $page, $pagesize);
		return $result;
	}

	/**
	 * 获取软连接的网址
	 * 
	 * @param  integer $aid     AID
	 * @param  boolean $protocol 是否有域名部分
	 * @return string
	 */
	public function get_symlink_url($aid, $protocol = NULL)
	{
		$path = $this->_create_symlink($aid);
		if (empty($path))
			return FALSE;

		return URL::site(str_replace(APPPATH, '', $path), $protocol, FALSE);
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
	 * @param  integer $aid      AID
	 * @param  boolean $protocol 是否有域名部分
	 * @param  string $filename  需要放在网址结尾的文件名,用以欺骗浏览器
	 * @return string
	 */
	public function get_url($aid, $protocol = NULL, $filename = NULL)
	{
		return  URL::site(!empty($filename) ? 'attachment/index/'.$aid.'/'.urlencode($filename) : 'attachment?aid='.$aid, $protocol, FALSE);
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
			$file = $this->get_file_bybasename($basename);
		} while (!empty($file));
		return $basename;
	}

	/**
	 * 在cache目录下创建一个软连接
	 * 
	 * @param  integer $aid AID
	 * @return string
	 */
	protected function _create_symlink($aid, $life_time = Date::DAY)
	{
		$data = $this->get($aid);
		if (empty($data))
			return FALSE;
		//将云端数据同步到本地
		$this->remote && $this->sync($aid);
		$path = Kohana::$cache_dir.DIRECTORY_SEPARATOR.'attachment,'.md5($aid).'.'.$data['ext'];
		!file_exists($path) && @symlink($this->get_real_rpath($data['path']), $path);
		return $path;
	}

	/**
	 * 删除软连接
	 * 
	 * @param  integer $aid AID
	 * @return
	 */
	public function unlink_symlink($aid)
	{
		$data = $this->get($aid);
		if (empty($data))
			return FALSE;
		$path = Kohana::$cache_dir.DIRECTORY_SEPARATOR.'attachment,'.md5($aid).'.'.$data['ext'];
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

	public function sync($aid, $life_time = NULL)
	{
		if ($this->_config['remote']['enabled'])
		{
			$data = $this->get($aid);
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